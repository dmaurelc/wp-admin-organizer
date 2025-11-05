/**
 * Admin JavaScript for the WP Admin Organizer plugin
 *
 * @package    WP_Admin_Organizer
 * @subpackage WP_Admin_Organizer/admin/js
 */

(function ($) {
  "use strict";

  // Variable to store the media uploader instance
  var mediaUploader;

  /**
   * Initialize the admin functionality
   */
  $(function () {
    // Initialize sortable for menu items
    $(".wp-admin-organizer-menu-list").sortable({
      placeholder: "wp-admin-organizer-menu-item ui-sortable-placeholder",
      update: function (event, ui) {
        // Update position numbers
        updatePositionNumbers();
      },
    });

    // Initialize dialog for messages
    $(".wp-admin-organizer-dialog").dialog({
      autoOpen: false,
      modal: true,
      width: 400,
      buttons: {
        OK: function () {
          $(this).dialog("close");
        },
      },
    });

    // Handle separator type change
    $("#separator-type").on("change", function () {
      if ($(this).val() === "text") {
        $(".text-field").addClass("visible");
        // Focus on the text field for better UX
        setTimeout(function () {
          $("#separator-text").focus();
        }, 100);
      } else {
        $(".text-field").removeClass("visible");
      }
    });

    // Handle save button click
    $("#save-menu-order").on("click", function (e) {
      e.preventDefault();
      saveMenuOrder();
    });

    // Handle add separator button click
    $("#add-separator").on("click", function (e) {
      e.preventDefault();
      addSeparator();
    });

    // Handle reset button click
    $("#reset-menu-order").on("click", function (e) {
      e.preventDefault();
      if (confirm(wp_admin_organizer.strings.confirm_reset)) {
        resetMenuOrder();
      }
    });

    // Handle remove separator button click
    $(".wp-admin-organizer-menu-list").on(
      "click",
      ".remove-separator",
      function (e) {
        e.preventDefault();
        $(this).closest(".wp-admin-organizer-separator-item").remove();
        updatePositionNumbers();
      }
    );

    // Handle text separator title click for inline editing
    $(".wp-admin-organizer-menu-list").on(
      "click",
      ".text-separator .wp-admin-organizer-separator-item-title",
      function (e) {
        e.preventDefault();
        var $item = $(this).closest(".wp-admin-organizer-separator-item");
        var currentText = $item.find(".separator-text").text();

        // Create an input field for editing
        var $input = $(
          '<input type="text" class="edit-separator-text" value="' +
            currentText +
            '">'
        );
        $(this).hide().after($input);
        $input.focus();

        // Handle input blur and enter key
        $input.on("blur keypress", function (e) {
          if (e.type === "blur" || (e.type === "keypress" && e.which === 13)) {
            var newText = $(this).val();
            $item.find(".separator-text").text(newText);
            var positionNum = $item.find(".position").val();
            $item
              .find(".wp-admin-organizer-separator-item-title")
              .text(
                "#" +
                  positionNum +
                  " - " +
                  (newText ? "Text Separator: " + newText : "Simple Separator")
              )
              .show();
            $(this).remove();
          }
        });
      }
    );

    // Initialize position numbers
    updatePositionNumbers();

    // Handle logo upload button click
    $("#upload-logo-button").on("click", function (e) {
      e.preventDefault();

      // If the media uploader instance already exists, open it
      if (mediaUploader) {
        mediaUploader.open();
        return;
      }

      // Create the media uploader
      mediaUploader = wp.media({
        title: wp_admin_organizer.strings.select_logo || "Select Logo",
        button: {
          text: wp_admin_organizer.strings.use_this_logo || "Use this logo",
        },
        multiple: false,
      });

      // When a logo is selected, run a callback
      mediaUploader.on("select", function () {
        var attachment = mediaUploader
          .state()
          .get("selection")
          .first()
          .toJSON();
        var logoUrl = attachment.url;

        // Update the hidden input field
        $("#admin-logo").val(logoUrl);

        // Update the preview image
        if ($(".logo-preview").length) {
          $(".logo-preview").attr("src", logoUrl);
        } else {
          $(".logo-preview-container").html(
            '<img src="' + logoUrl + '" alt="Admin Logo" class="logo-preview">'
          );
        }

        // Show the remove button if it doesn't exist
        if (!$("#remove-logo-button").length) {
          $("#upload-logo-button").after(
            '<button id="remove-logo-button" class="button">Remove</button>'
          );
        }

        // Save the logo URL via AJAX
        saveLogo(logoUrl);
      });

      // Open the uploader dialog
      mediaUploader.open();
    });

    // Handle remove logo button click (using event delegation for dynamically added button)
    $(document).on("click", "#remove-logo-button", function (e) {
      e.preventDefault();

      // Clear the hidden input field
      $("#admin-logo").val("");

      // Remove the preview image
      $(".logo-preview-container").empty();

      // Remove the remove button
      $(this).remove();

      // Save the empty logo URL via AJAX
      saveLogo("");
    });

    // Handle hide/show toggle button click
    $(".wp-admin-organizer-menu-list").on(
      "click",
      ".toggle-visibility",
      function (e) {
        e.preventDefault();
        var $item = $(this).closest(".wp-admin-organizer-menu-item");
        $item.toggleClass("hidden");

        // Update button icon/text
        if ($item.hasClass("hidden")) {
          $(this).html('<span class="dashicons dashicons-hidden"></span>');
          $(this).attr("title", "Show");
        } else {
          $(this).html('<span class="dashicons dashicons-visibility"></span>');
          $(this).attr("title", "Hide");
        }
      }
    );

    // Handle menu item title click for inline editing (rename functionality)
    $(".wp-admin-organizer-menu-list").on(
      "click",
      ".wp-admin-organizer-menu-item-title",
      function (e) {
        e.preventDefault();
        var $item = $(this).closest(".wp-admin-organizer-menu-item");
        var currentText = $(this).text();

        // Remove position number from text
        var textWithoutPosition = currentText.replace(/^#\d+\s*-\s*/, "");

        // Create an input field for editing
        var $input = $(
          '<input type="text" class="edit-menu-item-title" value="' +
            textWithoutPosition +
            '">'
        );
        $(this).hide().after($input);
        $input.focus().select();

        // Handle input blur and enter key
        $input.on("blur keypress", function (e) {
          if (e.type === "blur" || (e.type === "keypress" && e.which === 13)) {
            var newText = $(this).val();
            var positionNum = $item.find(".position").val();

            // Store the custom name as a data attribute
            $item.data("custom-name", newText);

            // Update the title
            $item
              .find(".wp-admin-organizer-menu-item-title")
              .text("#" + positionNum + " - " + newText)
              .show();
            $(this).remove();
          }
        });
      }
    );

    // Handle export button click
    $("#export-config").on("click", function (e) {
      e.preventDefault();
      exportConfiguration();
    });

    // Handle import button click
    $("#import-config").on("click", function (e) {
      e.preventDefault();
      $("#import-config-file").click();
    });

    // Handle file selection for import
    $("#import-config-file").on("change", function (e) {
      var file = e.target.files[0];
      if (file) {
        var reader = new FileReader();
        reader.onload = function (e) {
          try {
            var config = e.target.result;
            // Validate JSON
            JSON.parse(config);
            importConfiguration(config);
          } catch (error) {
            showMessage("Invalid configuration file. Please select a valid JSON file.");
          }
        };
        reader.readAsText(file);
      }
    });
  });

  /**
   * Save the logo URL via AJAX
   */
  function saveLogo(logoUrl) {
    $.ajax({
      url: wp_admin_organizer.ajax_url,
      type: "POST",
      data: {
        action: "save_logo",
        nonce: wp_admin_organizer.nonce,
        logo_url: logoUrl,
      },
      success: function (response) {
        // Don't show any message for logo updates
        // The UI will update automatically
      },
      error: function () {
        // Only show error messages
        showMessage(
          wp_admin_organizer.strings.logo_save_error || "Error saving logo."
        );
      },
    });
  }

  /**
   * Update position numbers for menu items
   */
  function updatePositionNumbers() {
    $(".wp-admin-organizer-menu-item, .wp-admin-organizer-separator-item").each(
      function (index) {
        var positionNum = index + 1;
        $(this).find(".position").text(positionNum).val(positionNum);
        $(this).find('input[name="position"]').val(index);

        // Update the title to include the position number
        if ($(this).hasClass("wp-admin-organizer-menu-item")) {
          var title = $(this)
            .find(".wp-admin-organizer-menu-item-title")
            .text();
          if (title.indexOf("#") !== 0) {
            var newTitle = "#" + positionNum + " - " + title;
            $(this).find(".wp-admin-organizer-menu-item-title").text(newTitle);
          } else {
            var titleParts = title.split(" - ");
            if (titleParts.length > 1) {
              var newTitle =
                "#" + positionNum + " - " + titleParts.slice(1).join(" - ");
              $(this)
                .find(".wp-admin-organizer-menu-item-title")
                .text(newTitle);
            }
          }
        } else if ($(this).hasClass("wp-admin-organizer-separator-item")) {
          var title = $(this)
            .find(".wp-admin-organizer-separator-item-title")
            .text();
          var text = $(this).find(".separator-text").text();
          var type = $(this).hasClass("text-separator") ? "text" : "simple";
          var newTitle =
            "#" +
            positionNum +
            " - " +
            (type === "text" ? "Text Separator: " + text : "Simple Separator");
          $(this)
            .find(".wp-admin-organizer-separator-item-title")
            .text(newTitle);
        }
      }
    );
  }

  /**
   * Save the menu order via AJAX
   */
  function saveMenuOrder() {
    // Get all items (menu items and separators) in their current order
    var allItems = $(".wp-admin-organizer-menu-list").children();

    // Get the menu items
    var menuItems = [];
    $(".wp-admin-organizer-menu-item").each(function () {
      menuItems.push($(this).data("menu-id"));
    });

    // Get the separators
    var separators = [];

    // Process all items in their DOM order to maintain correct positioning
    allItems.each(function (index) {
      if ($(this).hasClass("wp-admin-organizer-separator-item")) {
        var type = $(this).hasClass("text-separator") ? "text" : "simple";
        var text = $(this).find(".separator-text").text();

        separators.push({
          position: index,
          type: type,
          text: text,
        });
      }
    });

    // Get hidden items
    var hiddenItems = [];
    $(".wp-admin-organizer-menu-item.hidden").each(function () {
      hiddenItems.push($(this).data("menu-id"));
    });

    // Get renamed items
    var renamedItems = {};
    $(".wp-admin-organizer-menu-item").each(function () {
      var menuId = $(this).data("menu-id");
      var customName = $(this).data("custom-name");
      if (customName) {
        renamedItems[menuId] = customName;
      }
    });

    // Send the AJAX request
    $.ajax({
      url: wp_admin_organizer.ajax_url,
      type: "POST",
      data: {
        action: "save_menu_order",
        nonce: wp_admin_organizer.nonce,
        menu_order: menuItems,
        separators: separators,
        hidden_items: hiddenItems,
        renamed_items: renamedItems,
      },
      success: function (response) {
        if (response.success) {
          // Show success modal and refresh the page after a delay
          showSuccessModal();

          // Set a timeout to refresh the page after the modal is shown
          setTimeout(function () {
            window.location.reload();
          }, 1500);
        } else {
          showMessage(wp_admin_organizer.strings.save_error);
        }
      },
      error: function () {
        showMessage(wp_admin_organizer.strings.save_error);
      },
    });
  }

  /**
   * Add a separator to the menu list
   */
  function addSeparator() {
    var type = $("#separator-type").val();
    var text = $("#separator-text").val();

    // Validate text input if type is 'text'
    if (type === "text" && text.trim() === "") {
      showMessage(
        wp_admin_organizer.strings.empty_separator_text ||
          "Please enter text for the separator"
      );
      $("#separator-text").focus();
      return;
    }

    var position = $(".wp-admin-organizer-menu-list").children().length;
    var positionNum = position + 1;

    // Create the separator item
    var separatorItem = $(
      '<div class="wp-admin-organizer-separator-item' +
        (type === "text" ? " text-separator" : "") +
        '">'
    );
    var separatorTitle = $(
      '<div class="wp-admin-organizer-separator-item-title">'
    ).text(
      "#" +
        positionNum +
        " - " +
        (type === "text" ? "Text Separator: " + text : "Simple Separator")
    );
    var separatorRemove = $('<a href="#" class="remove-separator">').text(
      "Remove"
    );
    var separatorText = $(
      '<span class="separator-text" style="display:none;">'
    ).text(text);
    var separatorPositionInput = $(
      '<input type="hidden" class="position" value="' + positionNum + '">'
    );
    var separatorPositionNameInput = $(
      '<input type="hidden" name="position">'
    ).val(position);

    // Append the elements to the separator item
    separatorItem.append(separatorTitle);
    separatorItem.append(separatorRemove);
    separatorItem.append(separatorText);
    separatorItem.append(separatorPositionInput);
    separatorItem.append(separatorPositionNameInput);

    // Add the separator to the menu list
    $(".wp-admin-organizer-menu-list").append(separatorItem);

    // Reset the form
    if (type === "text") {
      $("#separator-text").val("");
    }

    // Update position numbers
    updatePositionNumbers();
  }

  /**
   * Reset the menu order via AJAX
   */
  function resetMenuOrder() {
    $.ajax({
      url: wp_admin_organizer.ajax_url,
      type: "POST",
      data: {
        action: "reset_menu_order",
        nonce: wp_admin_organizer.nonce,
      },
      success: function (response) {
        if (response.success) {
          // Reload the page to show the default menu order
          location.reload();
        } else {
          showMessage(wp_admin_organizer.strings.reset_error);
        }
      },
      error: function () {
        showMessage(wp_admin_organizer.strings.reset_error);
      },
    });
  }

  /**
   * Show a message in the dialog
   */
  function showMessage(message) {
    $(".wp-admin-organizer-dialog").html(message).dialog("open");
  }

  /**
   * Show the success modal
   */
  function showSuccessModal() {
    $(".wp-admin-organizer-success-modal").addClass("visible");

    // Close the modal when the close button is clicked
    $("#success-modal-close").on("click", function () {
      $(".wp-admin-organizer-success-modal").removeClass("visible");
    });

    // Close the modal when clicking outside of it
    $(".wp-admin-organizer-success-modal").on("click", function (e) {
      if ($(e.target).hasClass("wp-admin-organizer-success-modal")) {
        $(this).removeClass("visible");
      }
    });

    // Auto-close after 3 seconds
    setTimeout(function () {
      $(".wp-admin-organizer-success-modal").removeClass("visible");
    }, 3000);
  }

  /**
   * Export configuration as JSON file
   */
  function exportConfiguration() {
    $.ajax({
      url: wp_admin_organizer.ajax_url,
      type: "POST",
      data: {
        action: "export_configuration",
        nonce: wp_admin_organizer.nonce,
      },
      success: function (response) {
        if (response.success) {
          // Create a JSON file and trigger download
          var dataStr =
            "data:text/json;charset=utf-8," +
            encodeURIComponent(JSON.stringify(response.data, null, 2));
          var downloadAnchorNode = document.createElement("a");
          downloadAnchorNode.setAttribute("href", dataStr);
          downloadAnchorNode.setAttribute(
            "download",
            "wp-admin-organizer-config.json"
          );
          document.body.appendChild(downloadAnchorNode);
          downloadAnchorNode.click();
          downloadAnchorNode.remove();
        } else {
          showMessage("Error exporting configuration.");
        }
      },
      error: function () {
        showMessage("Error exporting configuration.");
      },
    });
  }

  /**
   * Import configuration from JSON string
   */
  function importConfiguration(configJson) {
    $.ajax({
      url: wp_admin_organizer.ajax_url,
      type: "POST",
      data: {
        action: "import_configuration",
        nonce: wp_admin_organizer.nonce,
        configuration: configJson,
      },
      success: function (response) {
        if (response.success) {
          showSuccessModal();
          setTimeout(function () {
            window.location.reload();
          }, 1500);
        } else {
          showMessage("Error importing configuration.");
        }
      },
      error: function () {
        showMessage("Error importing configuration.");
      },
    });
  }
})(jQuery);
