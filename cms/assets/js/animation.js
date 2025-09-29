document.addEventListener("DOMContentLoaded", () => {
  const mainContent = document.getElementById("main-content");
  if (mainContent) {
    setTimeout(() => {
      mainContent.classList.add("content-visible");
    }, 60);
  }
});

