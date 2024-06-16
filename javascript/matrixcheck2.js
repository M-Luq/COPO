function checkTableExists() {
    var subjectCodeInput;
    subjectCodeInput = document.getElementById("subject_code");
    var subjectCode = subjectCodeInput.value;

    var xhr = new XMLHttpRequest();
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                var response = xhr.responseText;
                if (response === "exists") {
                   console.log("table exists");
                } else {
                    alert("Please mail admin, There is no matrix created for this subject: " + subjectCode);
                }
            } else {
                // Handle errors here
                alert("Error: " + xhr.status);
            }
        }
    };

    // Send a POST request to the PHP script to check the table
    xhr.open("POST", "../check_table.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send("subject_code=" + subjectCode);
}
