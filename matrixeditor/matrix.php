<?php 
session_start();
include (__DIR__.'/../database.php');
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
if (isset($_SESSION['code'])) {?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Table</title>
    <link rel="stylesheet" href="./style1.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
<script src="./matrixcheck.js"></script>
</head>
<body>
    <button class="matrixbtn" type="button" onclick="window.location.href='logout.php'">Logout</button>
    <?php 
    //You can set the where conditon to allow admin to add admins
    $checkEmailQuery = "SELECT email FROM admin where id=1";
    $checkStmt = $conn->prepare($checkEmailQuery);
    $checkStmt->execute();

    $emails = $checkStmt->fetchAll(PDO::FETCH_COLUMN, 0); // Extract emails from the result
    $emails = array_map('trim',$emails);
    if (in_array($_SESSION['email'], $emails)) {?>
    <button class="sadminbtn" style="top:42px;right: 250px; " type="button" onclick="window.location.href='target.php'">SET TARGET</button>
    <button class="sadminbtn" type="button" onclick="window.location.href='admin.php'">ADD MAIL</button>
    <?php } ?>
    <h1>Manage Table</h1>
    <form id="table-form">
        <label for="subject_code">Table Name:</label>
        <input type="text" id="subject_code" name="subject_code" required onkeydown="if (event.keyCode === 13) { checkTableExists(); return false; }" oninput="this.value = this.value.toUpperCase();"><br><br>
        <label for="coCount">Number of CO:</label>
        <input type="number" id="coCount" required><br>
        <label for="poCount">Number of PO:</label>
        <input type="number" id="poCount" required><br>
        <label for="psoCount">Number of PSO:</label>
        <input type="number" id="psoCount" required><br>
        <input type="submit" value="Create Table">
    </form>

    <div id="table-container"></div>

    <script>
        document.getElementById('table-form').addEventListener('submit', function (e) {
            e.preventDefault();

            const tableName = document.getElementById('subject_code').value;
            const coCount = parseInt(document.getElementById('coCount').value);
            const poCount = parseInt(document.getElementById('poCount').value);
            const psoCount = parseInt(document.getElementById('psoCount').value);

            const table = document.createElement('table');
            const headerRow = table.insertRow();
            headerRow.insertCell().textContent = 'CO';

            for (let i = 1; i <= poCount; i++) {
                headerRow.insertCell().textContent = `PO${i}`;
            }

            for (let i = 1; i <= psoCount; i++) {
                headerRow.insertCell().textContent = `PSO${i}`;
            }

            for (let i = 1; i <= coCount; i++) {
                const row = table.insertRow();
                const coCell = row.insertCell();
                coCell.contentEditable = false;
                coCell.textContent = `CO${i}`;

                for (let j = 1; j <= poCount + psoCount; j++) {
                    const cell = row.insertCell();
                    cell.contentEditable = true;
                }
            }

            const submitButton = document.createElement('input');
            submitButton.type = 'submit';
            submitButton.id = 'insert';
            submitButton.value = 'Insert Data';

            submitButton.addEventListener('click', function () {
                const rows = table.querySelectorAll('tr');
                const data = [];

                // for (let i = 1; i < rows.length; i++) {
                //     const cells = rows[i].querySelectorAll('td');
                //     const rowData = [cells[0].textContent];
                //     for (let j = 1; j < cells.length; j++) {
                //         rowData.push(cells[j].textContent);
                //     }
                //     data.push(rowData);
                // }
                for (let i = 1; i < rows.length; i++) {
                    const cells = rows[i].querySelectorAll('td');
                const rowData = [cells[0].textContent];
                for (let j = 1; j < cells.length; j++) {
                    // Check if the cell is empty
                    const cellContent = cells[j].textContent.trim();
                    if (cellContent === null) {
                        // If the cell is empty, push null to the rowData array
                        rowData.push(null);
                    } else {
                        // Otherwise, push the cell content
                        rowData.push(cellContent);
                    }
                }
                data.push(rowData);
               }

                // Send the 'data' array and 'tableName' to PHP using AJAX
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'matprocess.php', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        const responseText = xhr.responseText;

                        if (responseText.includes('created') || responseText.includes('exists')) {
                            alert(responseText);
                            window.location.href = "edit.php?table=" + tableName;
                        } else {
                            alert(responseText); // Show the response from PHP
                        }
                    }
                };

                xhr.send(JSON.stringify({ tableName: tableName, data: data, coCount: coCount, poCount: poCount, psoCount: psoCount }));
            });

            const tableContainer = document.getElementById('table-container');
            tableContainer.innerHTML = '';
            tableContainer.appendChild(table);
            tableContainer.appendChild(submitButton);
        });
        document.getElementById('table-container').addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const currentCell = e.target;
        const currentRow = currentCell.parentNode;
        const currentRowIndex = currentRow.rowIndex;
        const currentCellIndex = currentCell.cellIndex;
        const table = currentRow.parentNode;

        // Calculate the next cell to focus
        let nextCell;
        if (currentCellIndex === table.rows[0].cells.length - 1) {
            // If it's the last cell in the row, move to the next row's first cell
            if (currentRowIndex < table.rows.length - 1) {
                nextCell = table.rows[currentRowIndex + 1].cells[1];
            }
        } else {
            // Otherwise, move to the next cell in the same row
            nextCell = currentRow.cells[currentCellIndex + 1];
        }

        if (nextCell) {
            nextCell.focus();
        }
    }
});

    </script>
</body>
</html>
<?php
}
else{
  echo "<script>
                alert('You are unauthorized to use these website');
                window.location.href = '../index.php';
            </script>";
}
?>