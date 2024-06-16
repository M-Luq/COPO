
$(document).ready(function () {
  // Function to handle moving to the next input field
  function moveToNextInput(input) {
      // Find the next input field within the same question
      var $nextInput = input.closest('.question').next().find('input');

      // If there's no next input in the current question, check if there are more questions for the current student
      if (!$nextInput.length) {
          var $currentStudent = input.closest('.student');
          var $nextQuestion = input.closest('.question').nextAll('.question');

          // If there's a next question for the current student, find its first input
          if ($nextQuestion.length) {
              $nextInput = $nextQuestion.first().find('input:first');
          } else {
              // If there are no more questions for the current student, move to the next student's first question
              var $nextStudent = $currentStudent.next('.student');
              if ($nextStudent.length) {
                  $nextInput = $nextStudent.find('.question:first input');
              }
          }
      }

      // Check if there is a next input field and focus on it
      if ($nextInput.length) {
          $nextInput.focus();
      } 
  }


  // Listen for input events on all input types
  $("input").on('input', function () {
    var errorOccurred = false; // Initialize error flag

    // Check if the input type is "number"
    if ($(this).attr('type') === 'number') {
      // Get the maximum allowed mark from the input's 'max' attribute
      var maxMark = parseInt($(this).attr('max'));

      // Get the current value of the input
      var currentValue = parseInt($(this).val().trim());

      // Check if the current value exceeds the maximum allowed mark
      if (!isNaN(maxMark) && !isNaN(currentValue) && currentValue > maxMark) {
        // Show an error message, clear the invalid value, and set the error flag to true
        alert("Invalid input: Value exceeds the maximum allowed mark of " + maxMark + ".");
        $(this).val(""); // Clear the invalid value
        errorOccurred = true; // Set the error flag to true
      }
    }
    //   else if ($(this).attr('type') === 'text') {
    //   // Define a pattern for text input validation (only 'p', 'z', and 'f' allowed)
    //   var pattern = /^[pzf]+$/i; // 'p', 'z', and 'f' (case-insensitive) allowed

    //   // Get the current value of the input
    //   var currentValue = $(this).val();

    //   // Check if the current value matches the pattern
    //    if (!pattern.test(currentValue)) {
    //   if (currentValue !== "" && event.keyCode !== 8) { // Check that the value is not empty and Backspace is not pressed
    //     // Show an error message, clear the invalid value
    //     alert("Invalid input: Only 'p', 'z', and 'f' characters are allowed.");
    //     $(this).val(""); // Clear the invalid value
    //   }
    // }
    // }

    // Clear any previously set timers
    clearTimeout($(this).data('timer'));

    // Check if the current input field is not empty and no error occurred before setting the timer to move to the next input field
    if ($(this).val() !== "" && !errorOccurred) {
      // Set a timer to move to the next input field after 0.5 seconds (500 milliseconds)
      $(this).data('timer', setTimeout(function () {
        moveToNextInput($(this));
      }.bind(this), 500)); // 500 milliseconds (0.5 seconds)
    }
  });

  // Listen for the Escape key press
 $("input").on('keydown', function (e) {
   // Check if the key code is 27 (Esc key)
    if ($(this).attr('type') === 'number' && e.keyCode === 27 ) {
      // Handle Esc key for number input fields
      var $prevInput = $(this).closest('.question').prev().find('input');
      
      if (!$prevInput.length) {
        var $currentStudent = $(this).closest('.student');
        $prevInput = $currentStudent.prev().find('.question:last input');
      }
      
      if ($prevInput.length) {
        $prevInput.focus();
      }
    } 
    // else if ($(this).attr('type') === 'text' && e.keyCode === 27) {
    //   // Handle Esc key for text input fields
    // var currentIndex = $('input[type="text"]').index(this);

    // if (currentIndex > 0) {
    //   $('input[type="text"]').eq(currentIndex - 1).focus();
    // }
    // }
  
});


  
  
  function validateNumberInput(input) {
    // Remove non-numeric characters and prevent 'e' character from being entered
    input.value = input.value.replace(/[^0-9.]/g, '');
  }

  // Add event listeners to all number input fields
  var numberInputFields = document.querySelectorAll('input[type="number"]');
  numberInputFields.forEach(function (input) {
    input.addEventListener('input', function () {
      validateNumberInput(this);
    });
  });
});

