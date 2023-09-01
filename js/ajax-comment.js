$(document).ready(function () {
    $("#ContactForm").submit(function (e) {
        e.preventDefault(); // Prevent the default form submission

        var submitButton = $("#contact");
        submitButton.prop("disabled", true);
        submitButton.html("Sending...");

        // Get user input values and escape them to prevent XSS
        var message = escapeHtml($("#message").val());
        var username = escapeHtml($("#username").val());
        var email = escapeHtml($("#email").val());
        var subject = escapeHtml($("#subject").val());

        // Get the CSRF token from the hidden input field
        var csrfToken = $("#csrf_token").val();

        // URL to send the AJAX request to (decode safely)
        var apiUrl = decodeURIComponent(atob("aHR0cDovL2xvY2FsaG9zdC9IYWlyQ3UvSGFpckN1dC9waHAvcG9zdC5waHA="));

        // Create the data object for the AJAX request
        var requestData = {
            contact: true,
            message: message,
            username: username,
            email: email,
            subject: subject,
            csrf_token: csrfToken // Include CSRF token in the data
        };

        // Check for potential XSS attack in user inputs
        if (hasXssPayload(message) || hasXssPayload(username) || hasXssPayload(email) || hasXssPayload(subject)) {
            alert("XSS attack attempt detected!");
            return;
        }

        // Send the AJAX request
        $.ajax({
            method: "POST",
            url: apiUrl,
            data: requestData,
            dataType: "json", // Expect JSON response
            success: function(response) {
                console.log(response);
                if (response.success) {
                    // Open a jQuery UI dialog with the colored message
                    var dialogContent = "We will call you soon on this email: <span style='color: red;'>" + email + "</span> <br/><br/> Wait 5 seconds, and this page will automatically reload.";
                    var dialog = $("<div class='custom-dialog'>" + dialogContent + "</div>").dialog({
                        title: "Notification",
                        modal: true,
                        buttons: {
                            OK: {
                                text: "OK",
                                class: "dialog-ok-button", // Add the class to the button
                                click: function() {
                                    $(this).dialog("close");
                                }
                            }
                        }
                    });
            
                    // Automatically close the dialog after 5 seconds
                    setTimeout(function() {
                        dialog.dialog("close");
                        location.reload();
                    }, 5000);
                } else {
                    var errorMessage = $("<div>", {
                        class: "error-message",
                        text: response.message // Display the error message directly from the server
                    });
                    $("#notificationArea").html(errorMessage);
                    alert('Bad request');
                }
            },
            error: function (xhr, status, error) {
                var response = JSON.parse(xhr.responseText);
                if (response.success === false && response.message === "XSS attack attempt detected!") {
                    alert("Nice Catch");
                } else {
                    console.error(response); // Log the error for debugging
                    console.log("An error occurred while submitting your message.");
                }
            },
            complete: function () {
                submitButton.prop("disabled", false);
                submitButton.html("Send Message"); // Set button text back to the original
            }
        });
    });
});

// Function to escape HTML entities to prevent XSS
function escapeHtml(text) {
    var element = document.createElement('div');
    element.textContent = text;
    return element.innerHTML;
}

// Function to check for potential XSS payload
function hasXssPayload(input) {
    // You can add more checks as needed
    return /<script|alert|onerror|javascript:/i.test(input);
}
