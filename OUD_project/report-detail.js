(function () {
  const reports = {
    'Monthly performance report': ['August 2026', 'OUD Reserve', 'Occupancy, revenue, and operational summary.', 'Approved'],
    'Property budget review': ['Q3 2026', 'OUD Reserve', 'Quarterly budget allocation and forecast review.', 'Approved'],
    'Maintenance summary': ['August 2026', 'OUD Reserve', 'Completed maintenance activity and open work orders.', 'Approved'],
    'Market positioning report': ['Q2 2026', 'OUD Square', 'Market context and positioning across the mixed-use destination.', 'Approved'],
    'Destination progress report': ['August 2026', 'OUD Dunes', 'Progress overview for the completed mixed-use destination.', 'Approved'],
    'Development update': ['August 2026', 'La Perle by OUD', 'Development progress and current delivery milestones.', 'Pending review']
  };
  const key = new URLSearchParams(location.search).get('report') || 'Monthly performance report';
  const item = reports[key] || reports['Monthly performance report'];
  const title = document.querySelector('.detail-hero h1'); if (title) title.textContent = `${item[0]}.`;
  const eyebrow = document.querySelector('.detail-hero .eyebrow'); if (eyebrow) eyebrow.textContent = key;
  const copy = document.querySelector('.detail-hero p:not(.eyebrow)'); if (copy) copy.textContent = item[2];
  const heroDetails = document.querySelectorAll('.detail-hero .hero-side strong, .detail-hero .hero-side small'); if (heroDetails.length > 1) { heroDetails[0].textContent = item[3]; heroDetails[1].textContent = item[1]; }
  const metric = document.querySelector('.report-detail-grid .report-download-card p'); if (metric) metric.textContent = `${item[0]} · ${item[1]} · PDF`;
})();
