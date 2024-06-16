<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Question Setup</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../css/style2.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
    <script src="../javascript/student.js"></script>
    <style>
        /* Add your CSS styles here */
    </style>
</head>
<body>
    <h1 style="font-weight: bold;">Question Pattern Setup</h1>
    <form method = "post" action="../headers/navigate2.php">
        <?php
        
        $num_assessments = $_SESSION['num_assessments'];

        for ($i = 1; $i <= $num_assessments; $i++) {
            echo "<strong><h2>Assessment $i</h2></strong>";

            echo '<label>Question Type:</label>';
            echo "<label><input type='radio' id='fixed_$i' name='question_type_$i' value='fixed' checked> Fixed Questions</label>";
            echo "<label><input type='radio' id='custom_$i' name='question_type_$i' value='custom'> Custom Questions</label><br><br>";

            $inputfieldid = "num_questions_$i";
            echo "<label for='$inputfieldid'>Number of Questions:</label>";
            echo "<input type='number' class='assessment-input' id='$inputfieldid' name='$inputfieldid' required><br><br>";
        }
        ?>
        <input type="submit" value="N E X T">
        <br>
        <br>
        <label>If you want the excel template to enter marks (click here)</label>
        <br>
        <button class="button" type="button" onclick="window.location.href='sheet.html?from=pattern'">N A V I G A T E</button>
    </form>
    <script>
        // Add event listeners to the radio buttons for question type
        document.addEventListener("DOMContentLoaded", function() {
            <?php for ($i = 1; $i <= $num_assessments; $i++) { ?>
                var radioFixed<?php echo $i; ?> = document.getElementById('fixed_<?php echo $i; ?>');
                var radioCustom<?php echo $i; ?> = document.getElementById('custom_<?php echo $i; ?>');
                var inputField<?php echo $i; ?> = document.getElementById('num_questions_<?php echo $i; ?>');

                // Set initial value based on the radio button's initial state
                inputField<?php echo $i; ?>.value = radioFixed<?php echo $i; ?>.checked ? "8" : "";
                inputField<?php echo $i; ?>.readOnly = radioFixed<?php echo $i; ?>.checked;

                radioFixed<?php echo $i; ?>.addEventListener("change", function() {
                    inputField<?php echo $i; ?>.value = "8";
                    inputField<?php echo $i; ?>.readOnly = true;
                });

                radioCustom<?php echo $i; ?>.addEventListener("change", function() {
                    inputField<?php echo $i; ?>.value = "";
                    inputField<?php echo $i; ?>.readOnly = false;
                });
            <?php } ?>
        });
    </script>

<script>
    // Function to save the entered values in localStorage
    function saveValues() {
        <?php for ($i = 1; $i <= $num_assessments; $i++) { ?>
            var inputField<?php echo $i; ?> = document.getElementById('num_questions_<?php echo $i; ?>');
            var value<?php echo $i; ?> = inputField<?php echo $i; ?>.value;
            localStorage.setItem('value<?php echo $i; ?>', value<?php echo $i; ?>);
        <?php } ?>
    }

    // Function to load the stored values from localStorage
    function loadValues() {
        <?php for ($i = 1; $i <= $num_assessments; $i++) { ?>
            var inputField<?php echo $i; ?> = document.getElementById('num_questions_<?php echo $i; ?>');
            var storedValue<?php echo $i; ?> = localStorage.getItem('value<?php echo $i; ?>');
            if (storedValue<?php echo $i; ?> !== null) {
                inputField<?php echo $i; ?>.value = storedValue<?php echo $i; ?>;
                inputField<?php echo $i; ?>.readOnly = storedValue<?php echo $i; ?> === "8" ? true : false;
            }
        <?php } ?>
    }

    // Add an event listener to the form for when it's submitted
    document.addEventListener("DOMContentLoaded", function() {
        // Check if the page was refreshed (F5 or browser refresh button)
        if (performance.navigation.type === 1) {
            // Page was refreshed, so clear localStorage
            <?php for ($i = 1; $i <= $num_assessments; $i++) { ?>
                localStorage.removeItem('value<?php echo $i; ?>');
            <?php } ?>
        } else if (performance.navigation.type === 0) {
            <?php for ($i = 1; $i <= $num_assessments; $i++) { ?>
                localStorage.removeItem('value<?php echo $i; ?>');
            <?php } ?>             
        }
        else{
            
            loadValues();
            
        }

        var form = document.querySelector('form');
        form.addEventListener('submit', function() {
            saveValues(); // Save values when the form is submitted
        });
    });
</script>


</body>
</html>
