
     $(document).ready(function () {
    $(".tab").click(function () {
        // Get the data-tab attribute of the clicked tab
        var clickedTab = $(this).data("tab");
        
        // Toggle the display of the content for the specific tab
        $(".student[data-tab='" + clickedTab + "']").slideToggle();
        
        // Close other tabs
        $(".tab").not(this).each(function () {
            var otherTab = $(this).data("tab");
            if (otherTab !== clickedTab) {
                $(".student[data-tab='" + otherTab + "']").slideUp();
            }
        });
    });
});