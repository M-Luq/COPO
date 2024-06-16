function checkTableExists() {
    var subjectCode = document.getElementById("subject_code").value;
    var xhr = new XMLHttpRequest();
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                var response = xhr.responseText;
                if (response === "exists") {
                    var excelInput = document.getElementById("coCount");
                    excelInput.focus();
                } else {
                   alert("Please mail admin,There is no matrix created for these subject : " + subjectCode);
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