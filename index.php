<!DOCTYPE html>
<html>
<head>
    <title>Student Assessment and Assignment Form</title>
    <link rel="stylesheet" href="./css/style2.css"> 
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
    <script src="./javascript/matrixcheck.js"></script>
    <script src="./javascript/tickanimation.js"></script>
</head>
<body>
    <h1>Student Assessment and Assignment Form</h1> 
    <button class="matrixbtn" type="button" onclick="window.location.href='callback.php'">Matrix Editor</button>

    <!-- Add buttons for Theory and Lab -->
    <div class="tl-button">
        <button id="theoryBtn" class="active" onclick="showTheoryForm()">Theory</button>
        <button id="labBtn" onclick="showLabForm()">Lab</button>
    </div>

    <!-- Form for Theory -->
    <form id="theoryForm" action="./headers/naviagate.php" method="post" enctype="multipart/form-data">
        <label for="subject_code_t">Subject Code:</label>
        <input type="text" id="subject_code_t" name="subject_code" required onkeydown="if (event.keyCode === 13) { checkTableExists(); return false; }" oninput="this.value = this.value.toUpperCase();"><br><br>
        <label for="excel_file_t">Upload Namelist Excel:</label> <p>(Enter the regno and name in 1st and 2nd column and start from row 2 to avoid errors)</p>
        <input type="file" id="excel_file_t" name="excel_file_t" accept=".xls, .xlsx" required>
        <span id="tick_t" class="tick-animation" style="display: none;">✓</span><br><br>
        <label for="num_assessments">Number of Assessments:</label>
        <input type="number" id="num_assessments" name="num_assessments" required><br><br>
        <label for="num_assignments">Number of Assignments:</label>
        <input type="number" id="num_assignments" name="num_assignments" required><br><br>
        <input type="submit" value="N E X T"><br><br>
        <label for="btn" >If you have the Excel with marks already(click the below button)</label>
        <button  id="btn" class="button" type="button" onclick="window.location.href='./theory/sheet.html?from=index'">N A V I G A T E</button><br><br>
    </form>

    <!-- Form for Lab -->
    <form id="labForm" action="./labheaders/navigate.php" method="post" enctype="multipart/form-data" style="display: none;">
        <label for="subject_code_l">Subject Code:</label>
        <input type="text" id="subject_code_l" name="subject_code" required onkeydown="if (event.keyCode === 13) { checkTableExists(); return false; }" oninput="this.value = this.value.toUpperCase();"><br><br>
        <label for="excel_file_l">Upload Namelist Excel:</label> <p>(Enter the regno and name in 1st and 2nd column and start from row 2 to avoid errors)</p>
        <input type="file" id="excel_file_l" name="excel_file_l" accept=".xls, .xlsx" required>
        <span id="tick_l" class="tick-animation" style="display: none;">✓</span><br><br>
        <label for="num_mid_sem">Number of Mid Sem:</label>
        <input type="number" id="num_mid_sem" name="num_mid_sem" required><br><br>
        <label for="num_experiments">Number of Assessment(Experiments):</label>
        <input type="number" id="num_experiments" name="num_experiments" required><br><br>
        <input type="submit" value="N E X T"><br><br>
        <label for="btn" >If you have the Excel with marks already(click the below button)</label>
        <button id="btn" class="button" type="button" onclick="window.location.href='./lab/sheet.html?from=index'">N A V I G A T E</button><br><br>
    </form>
    
    

  
</body>
</html>
