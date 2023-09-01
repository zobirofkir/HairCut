$(document).ready(function() {
    $("#reserveButton").click(function(e) {
        e.preventDefault();

        var submitButton = $("#reserve");
        submitButton.prop("disabled", true);
        submitButton.html("Sending...");

        // Get form data
        var formData = {
            fullName: $("#fullName").val(),
            email: $("#email").val(),
            phoneNumber: $("#phoneNumber").val(),
            haircutType: $("#haircutType").val(),
            appointmentDate: $("#appointmentDate").val(),
            appointmentTime: $("#appointmentTime").val(),
            additionalNotes: $("#additionalNotes").val(),
            reserve: true // Include reserve in the form data
        };
        
        // Specify the URL directly (without decoding)
        var apiUrl = decodeURIComponent(atob("aHR0cDovL2xvY2FsaG9zdC9IYWlyQ3V0L0hhaXJDdXQvcGhwL3Jlc2VydmUtcGxhY2UucGhw"));

        // Send AJAX request
        $.ajax({
            type: "POST",
            url: apiUrl, // Use the plain URL
            data: formData,
            dataType: 'text', // Expect plain text response
            success: function(response) {
                alert(response); // Show the response directly
                // You can re-enable the button and change the text after receiving the response
                submitButton.prop("disabled", false);
                submitButton.html("Submit");
            },
            error: function(xhr, textStatus, errorThrown) {
                alert("Error: " + errorThrown); // Show AJAX error
                // In case of an error, re-enable the button and reset the text
                submitButton.prop("disabled", false);
                submitButton.html("Submit");
            }
        });
    });
});
