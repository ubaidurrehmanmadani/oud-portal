(function () {
  const requests = {
    'Lobby maintenance budget': { property: 'OUD Reserve', amount: 'SAR 145,000', type: 'Maintenance budget', submitted: 'Property Management', file: 'Maintenance budget proposal.pdf' },
    'Tenant fit-out request': { property: 'OUD Reserve', amount: 'SAR 82,000', type: 'Tenant fit-out', submitted: 'Property Management', file: 'Tenant fit-out request.pdf' },
    'Special event approval': { property: 'OUD Square', amount: 'SAR 28,500', type: 'Special event', submitted: 'Hospitality Management', file: 'Special event proposal.pdf' },
    'Landscape maintenance renewal': { property: 'OUD Dunes', amount: 'SAR 64,000', type: 'Landscape maintenance', submitted: 'Property Management', file: 'Landscape renewal proposal.pdf' },
    'Retail signage request': { property: 'OUD Square', amount: 'SAR 39,500', type: 'Retail signage', submitted: 'Commercial Division', file: 'Retail signage request.pdf' },
    'Construction milestone review': { property: 'La Perle by OUD', amount: 'SAR 310,000', type: 'Construction milestone', submitted: 'Development Management', file: 'Construction milestone report.pdf' }
  };
  const key = new URLSearchParams(location.search).get('request') || 'Lobby maintenance budget';
  const item = requests[key] || requests['Lobby maintenance budget'];
  const title = document.querySelector('.detail-hero h1'); if (title) title.textContent = `${key}.`;
  const copy = document.querySelector('.detail-hero p:not(.eyebrow)'); if (copy) copy.textContent = `Review the ${item.type.toLowerCase()} request and supporting document before making a decision.`;
  const values = document.querySelectorAll('.property-details dd'); if (values.length >= 4) { values[0].textContent = item.property; values[1].textContent = item.amount; values[2].textContent = item.type; values[3].textContent = item.submitted; }
  const file = document.querySelector('.supporting-file strong'); if (file) file.textContent = item.file;
})();
