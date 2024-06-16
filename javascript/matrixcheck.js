function checkTableExists() {
    var subjectCodeInput;
    var excelInput;
    
    if (document.getElementById("theoryForm").style.display !== "none") {
        subjectCodeInput = document.getElementById("subject_code_t");
        excelInput = document.getElementById("excel_file_t");
    } else {
        subjectCodeInput = document.getElementById("subject_code_l");
        excelInput = document.getElementById("excel_file_l");
    }

    var subjectCode = subjectCodeInput.value;

    var xhr = new XMLHttpRequest();
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                var response = xhr.responseText;
                if (response === "exists") {
                    excelInput.focus();
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
    xhr.open("POST", "./check_table.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.send("subject_code=" + subjectCode);
}
