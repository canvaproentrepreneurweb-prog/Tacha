(function () {
  var menuToggle = document.getElementById("menuToggle");
  var mainNav = document.getElementById("mainNav");
  var quickTrackForm = document.getElementById("quickTrackForm");

  if (menuToggle && mainNav) {
    menuToggle.addEventListener("click", function () {
      mainNav.classList.toggle("open");
    });
  }

  if (quickTrackForm) {
    quickTrackForm.addEventListener("submit", function (event) {
      event.preventDefault();
      var trackingInput = document.getElementById("trackingInput");
      var value = trackingInput.value.trim();
      if (!value) return;
      window.location.href = "tracking.html?tracking=" + encodeURIComponent(value);
    });
  }
})();
