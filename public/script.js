document.addEventListener("DOMContentLoaded", () => {
    console.log("Task Management Website Loaded!");
  
    // Example: Toggle dark mode
    const darkModeToggle = document.querySelector("#dark-mode-toggle");
    if (darkModeToggle) {
        darkModeToggle.addEventListener("click", () => {
            document.body.classList.toggle("dark");
        });
    }
});
