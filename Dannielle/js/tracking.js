(function () {
  var data = window.CARGOLINK_TRACKING_DATA || {};
  var form = document.getElementById("trackingSearchForm");
  var input = document.getElementById("trackingSearchInput");
  var result = document.getElementById("trackingResult");

  function esc(value) {
    return String(value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function renderNotFound(trackingNumber) {
    result.classList.remove("hidden");
    result.innerHTML =
      '<article class="panel">' +
      "<h2>Aucun resultat</h2>" +
      "<p>Le numero <strong>" + esc(trackingNumber) + "</strong> est introuvable.</p>" +
      "<p>Verifiez le format ou contactez le support.</p>" +
      "</article>";
  }

  function renderShipment(trackingNumber, shipment) {
    var historyHtml = shipment.history
      .map(function (item) {
        return (
          '<div class="timeline-item ' + (item.done ? "done" : "") + '">' +
          "<p><strong>" + esc(item.status) + "</strong></p>" +
          "<p>" + esc(item.location) + "</p>" +
          "<p>" + esc(item.date) + "</p>" +
          "</div>"
        );
      })
      .join("");

    result.classList.remove("hidden");
    result.innerHTML =
      "<h2>Resultat tracking</h2>" +
      '<div class="summary-grid">' +
      '<article class="panel"><strong>Numero</strong>' + esc(trackingNumber) + "</article>" +
      '<article class="panel"><strong>Statut actuel</strong><span class="status-badge">' + esc(shipment.currentStatus) + "</span></article>" +
      '<article class="panel"><strong>Expediteur</strong>' + esc(shipment.sender) + "</article>" +
      '<article class="panel"><strong>Destinataire</strong>' + esc(shipment.recipient) + "</article>" +
      '<article class="panel"><strong>Origine</strong>' + esc(shipment.origin) + "</article>" +
      '<article class="panel"><strong>Destination</strong>' + esc(shipment.destination) + "</article>" +
      '<article class="panel"><strong>ETA</strong>' + esc(shipment.eta) + "</article>" +
      '<article class="panel"><strong>Type</strong>' + esc(shipment.shipmentType) + "</article>" +
      "</div>" +
      '<article class="panel">' +
      "<h3>Historique des evenements</h3>" +
      '<div class="timeline">' + historyHtml + "</div>" +
      "</article>";
  }

  function search(trackingNumber) {
    var clean = (trackingNumber || "").trim();
    if (!clean) return;
    input.value = clean;

    var shipment = data[clean];
    if (!shipment) {
      renderNotFound(clean);
      return;
    }

    renderShipment(clean, shipment);
  }

  if (form) {
    form.addEventListener("submit", function (event) {
      event.preventDefault();
      search(input.value);
    });
  }

  var query = new URLSearchParams(window.location.search);
  var initial = query.get("tracking");
  if (initial) {
    search(initial);
  }
})();
