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
        .box {
            cursor: pointer;
    padding: 20px;
    background-color: #ddd;
    border-radius: 5px;
    margin-bottom: 10px;
        }
        .box h3 {
            text-align:center;
        }
        body::-webkit-scrollbar {
      width: 0; 
    }
    
    body::-webkit-scrollbar-thumb {
      background-color: transparent; /* Color of the scrollbar thumb */
    }
    

.bodyclass{
    display: flex;
    flex-direction: column;
}

    </style>
</head>
<body>
<div class="container">
    <h1>Question mark and co allocation</h1>
    
    <?php
     // Start the session

    $num_mid_sem = $_SESSION['num_mid_sem'];
    $num_experiments = $_SESSION['num_experiments'];
    $co_no = $_SESSION['co_no'];

    echo "<form class='qform' action='process.php' method='post'>";

    for ($i = 1; $i <= $num_mid_sem; $i++) {
        $sessionqn = "num_questions_$i";
        $num_questions = $_SESSION[$sessionqn];

        echo "<div class='box' id='assessment_${i}_box'>";
        echo "<h2>Mid Sem $i</h2>";
        echo "</div>";
        echo "<div class='form' id='assessment_${i}_form' style='display:none;'>";

        for ($q = 1; $q <= $num_questions; $q++) {
            $num_questions = $_SESSION[$sessionqn];
            echo "<h3>Question $q</h3>";
            echo "<label for='num_subdivisions_${i}_$q'>Number of Subdivisions:</label>";
            echo "<input type='number' id='num_subdivisions_${i}_$q' name='num_subdivisions_${i}_$q' min='1' value='1'><br><br>";
            echo "<div id='subdivisions_${i}_$q'>";
            echo "</div>";
        }

        echo "</div>";
    }?>

    <script>
$(document).ready(function() {
    // Handle click events for assessment boxes
    <?php for ($i = 1; $i <= $num_mid_sem; $i++) { ?>
        $("#assessment_<?php echo $i; ?>_box").click(function() {
            $("#assessment_<?php echo $i; ?>_form").slideToggle();
        });
    <?php } ?>
});
</script>

    
<script>
$(document).ready(function() {
    <?php for ($i = 1; $i <= $num_mid_sem; $i++) { 
        $sessionqn = "num_questions_$i";
        $num_questions = $_SESSION[$sessionqn]; ?>

//for fixed question
        <?php if($num_questions == 8){?> //you can set the fixed questions here
            defaultmarks=[2,2,2,2,2,5,5,5]; // the question marks template
          <?php  for ($q = 1; $q <= $num_questions; $q++) { ?>
            $("#num_subdivisions_<?php echo $i; ?>_<?php echo $q; ?>").change(function() {
                var numSubdivisions = parseInt($(this).val());
                var subdivisionsDiv = $("#subdivisions_<?php echo $i; ?>_<?php echo $q; ?>");
                subdivisionsDiv.html('');

                if (numSubdivisions > 1) {
                    for (var s = 1; s <= numSubdivisions; s++) {
                        subdivisionsDiv.append('<h4>Subdivision ' + s + '</h4>' +
                            '<label for="subdivision_mark_<?php echo $i; ?>_<?php echo $q; ?>_' + s + '">Subdivision Mark Weightage:</label>' +
                            '<input type="number" id="subdivision_mark_<?php echo $i; ?>_<?php echo $q; ?>_' + s + '" name="subdivision_mark_<?php echo $i; ?>_<?php echo $q; ?>_' + s + '" step="0.5"  required><br><br>' +
                            '<label for="subdivision_category_<?php echo $i; ?>_<?php echo $q; ?>_' + s + '">CO:</label>' +
                            '<select id="subdivision_category_<?php echo $i; ?>_<?php echo $q; ?>_' + s + '" name="subdivision_category_<?php echo $i; ?>_<?php echo $q; ?>_' + s + '" required>' +
                            <?php
                            // Generate the options dynamically based on co_no
                            for ($co = 1; $co <= $co_no; $co++) {
                                echo "'<option value=\"co$co\">CO$co</option>' +";
                            }
                            ?>
                            '</select><br><br>');
                    }
                } else {
                    subdivisionsDiv.html('<label for="subdivision_mark_<?php echo $i; ?>_<?php echo $q; ?>">Mark Weightage:</label>' +
                        '<input type="number" id="subdivision_mark_<?php echo $i; ?>_<?php echo $q; ?>" name="subdivision_mark_<?php echo $i; ?>_<?php echo $q; ?>" step="0.5" value="' + defaultmarks[<?php echo $q - 1; ?>] + '" required><br><br>' +
                        '<label for="subdivision_category_<?php echo $i; ?>_<?php echo $q; ?>">CO:</label>' +
                        '<select id="subdivision_category_<?php echo $i; ?>_<?php echo $q; ?>" name="subdivision_category_<?php echo $i; ?>_<?php echo $q; ?>" required>' +
                        <?php
                        // Generate the options dynamically based on co_no
                        for ($co = 1; $co <= $co_no; $co++) {
                            echo "'<option value=\"co$co\">CO$co</option>' +";
                        }
                        ?>
                        '</select><br><br>');
                }
            });

            $("#num_subdivisions_<?php echo $i; ?>_<?php echo $q; ?>").trigger('change');
        <?php } ?>

       <?php }

//For custom questions 
       else{
        for ($q = 1; $q <= $num_questions; $q++) { ?>
            $("#num_subdivisions_<?php echo $i; ?>_<?php echo $q; ?>").change(function() {
                var numSubdivisions = parseInt($(this).val());
                var subdivisionsDiv = $("#subdivisions_<?php echo $i; ?>_<?php echo $q; ?>");
                subdivisionsDiv.html('');

                if (numSubdivisions > 1) {
                    for (var s = 1; s <= numSubdivisions; s++) {
                        subdivisionsDiv.append('<h4>Subdivision ' + s + '</h4>' +
                            '<label for="subdivision_mark_<?php echo $i; ?>_<?php echo $q; ?>_' + s + '">Subdivision Mark Weightage:</label>' +
                            '<input type="number" id="subdivision_mark_<?php echo $i; ?>_<?php echo $q; ?>_' + s + '" name="subdivision_mark_<?php echo $i; ?>_<?php echo $q; ?>_' + s + '" step="0.5" required><br><br>' +
                            '<label for="subdivision_category_<?php echo $i; ?>_<?php echo $q; ?>_' + s + '">CO:</label>' +
                            '<select id="subdivision_category_<?php echo $i; ?>_<?php echo $q; ?>_' + s + '" name="subdivision_category_<?php echo $i; ?>_<?php echo $q; ?>_' + s + '" required>' +
                            <?php
                            // Generate the options dynamically based on co_no
                            for ($co = 1; $co <= $co_no; $co++) {
                                echo "'<option value=\"co$co\">CO$co</option>' +";
                            }
                            ?>
                            '</select><br><br>');
                    }
                } else {
                    subdivisionsDiv.html('<label for="subdivision_mark_<?php echo $i; ?>_<?php echo $q; ?>">Mark Weightage:</label>' +
                        '<input type="number" id="subdivision_mark_<?php echo $i; ?>_<?php echo $q; ?>" name="subdivision_mark_<?php echo $i; ?>_<?php echo $q; ?>" step="0.5" required><br><br>' +
                        '<label for="subdivision_category_<?php echo $i; ?>_<?php echo $q; ?>">CO:</label>' +
                        '<select id="subdivision_category_<?php echo $i; ?>_<?php echo $q; ?>" name="subdivision_category_<?php echo $i; ?>_<?php echo $q; ?>" required>' +
                        <?php
                        // Generate the options dynamically based on co_no
                        for ($co = 1; $co <= $co_no; $co++) {
                            echo "'<option value=\"co$co\">CO$co</option>' +";
                        }
                        ?>
                        '</select><br><br>');
                }
            });

            $("#num_subdivisions_<?php echo $i; ?>_<?php echo $q; ?>").trigger('change');
        <?php }
 } ?>
    <?php } ?>
});
</script>

<?php
    for ($i = 1; $i <= $num_experiments; $i++) {
    echo "<div class='box' id='assignment_${i}_box'>";
echo "<h2>Experiment $i</h2>";
echo "</div>";
echo "<div class='assignment-form' id='assignment_${i}_form' style='display:none;'>";

// Generating CO labels and input fields for assignment
for ($k = 1; $k <= $co_no; $k++) {
    
    echo "<label for='co${k}_${i}_mark'>CO${k}:</label>";
    echo "<input type='number' id='co${k}_${i}_mark' name='co${k}_${i}_mark' step='any' required><br><br>";   
 }
echo "</div>";
    }?>


    <script>
    $(document).ready(function() {
    
        // Handle click events for assignment boxes
        <?php for ($i = 1; $i <= $num_experiments; $i++) { ?>
            $("#assignment_<?php echo $i; ?>_box").click(function() {
                $("#assignment_<?php echo $i; ?>_form").slideToggle();
            });
        <?php } ?>
    });
    </script>
    <?php
    


    echo "<input type='submit' value='N E X T'>";
    echo "</form>";
    ?>
</div>

</body>
</html>
