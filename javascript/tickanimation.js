document.addEventListener("DOMContentLoaded", function () {
    // Select the file input and the tick element for Theory form
    var theoryFileInput = document.getElementById("excel_file_t");
    var theoryTickElement = document.getElementById("tick_t");

    // Add an event listener to the file input for Theory form
    if (theoryFileInput) {
        theoryFileInput.addEventListener("change", function () {
            console.log("Theory file selected");
            if (theoryFileInput.files.length > 0) {
                // Display the tick animation element for Theory form
                theoryTickElement.style.display = "inline-block";
            } else {
                // Hide the tick animation element if no file is selected for Theory form
                theoryTickElement.style.display = "none";
            }
        });
    }

    // Select the file input and the tick element for Lab form
    var labFileInput = document.getElementById("excel_file_l");
    var labTickElement = document.getElementById("tick_l");

    // Add an event listener to the file input for Lab form
    if (labFileInput) {
        labFileInput.addEventListener("change", function () {
            console.log("Lab file selected");
            if (labFileInput.files.length > 0) {
                // Display the tick animation element for Lab form
                labTickElement.style.display = "inline-block";
            } else {
                // Hide the tick animation element if no file is selected for Lab form
                labTickElement.style.display = "none";
            }
        });
    }
});

function showTheoryForm() {
    document.getElementById("theoryForm").style.display = "block";
    document.getElementById("labForm").style.display = "none";
    document.getElementById("theoryBtn").classList.add("active");
    document.getElementById("labBtn").classList.remove("active");
}

function showLabForm() {
    document.getElementById("theoryForm").style.display = "none";
    document.getElementById("labForm").style.display = "block";
    document.getElementById("labBtn").classList.add("active");
    document.getElementById("theoryBtn").classList.remove("active");
}
