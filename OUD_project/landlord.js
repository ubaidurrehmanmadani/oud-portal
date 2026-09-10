(function () {
  const select = document.querySelector('#property-select');
  if (select) {
    const pills = document.createElement('div'); pills.className = 'property-pills';
    [...select.options].forEach((option, index) => { const button = document.createElement('button'); button.type = 'button'; button.textContent = option.textContent; button.className = index === 0 ? 'active' : ''; button.addEventListener('click', () => { select.value = option.value; pills.querySelectorAll('button').forEach((item) => item.classList.toggle('active', item === button)); select.dispatchEvent(new Event('change')); }); pills.append(button); });
    select.hidden = true; select.parentElement.append(pills);
    select.addEventListener('change', () => {
    const title = document.querySelector('.landlord-hero h1');
    const card = document.querySelector('.property-card h2');
    if (title) title.textContent = `${select.value}.`;
    if (card) card.textContent = select.value;
    activeProperty = select.value;
    const property = propertyData[activeProperty] || propertyData['OUD Reserve'];
    document.querySelectorAll('.landlord-metrics .metric').forEach((metric, index) => { const values = [property.occupancy, property.revenue, property.area, property.approvals]; if (metric.querySelector('strong')) metric.querySelector('strong').textContent = values[index]; });
    const details = document.querySelectorAll('.property-details dd'); if (details.length >= 4) { details[0].textContent = property.type; details[1].textContent = property.location; details[2].textContent = `${property.units} units`; details[3].textContent = 'Owner access'; }
    const hero = document.querySelector('.landlord-hero'); if (hero) hero.style.backgroundImage = `linear-gradient(90deg, rgba(94,102,85,.92), rgba(94,102,85,.62)), url("${property.image}")`;
    if (chart) draw(currentMonths);
    });
  }
  const chart = document.querySelector('.chart');
  if (!chart) return;
  let currentMonths = 12;
  let activeProperty = 'OUD Reserve';
  const labels = ['Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'];
  const propertyData = {
    'OUD Reserve': {
      occupancy: '86%', revenue: 'SAR 2.4M', area: '18,420 sqm', units: 54, approvals: 3, type: 'Mixed use', location: 'Riyadh, Saudi Arabia', image: 'assets/oud-homepage/https___images.squarespace-cdn.com_content_v1_68f677afba491e524acf3912_1760982980319-3DKG1FJOBVCMKTNWBZVE_OUD_Reserve0006.jpg',
      series: [55, 57, 60, 58, 61, 64, 63, 67, 71, 69, 74, 86]
    },
    'OUD Square': {
      occupancy: '78%', revenue: 'SAR 1.8M', area: '14,760 sqm', units: 38, approvals: 2, type: 'Mixed use', location: 'Riyadh, Saudi Arabia', image: 'assets/oud-homepage/https___images.squarespace-cdn.com_content_v1_68f677afba491e524acf3912_1760982980408-77ID3JFJD7KUORUD7S1J_AU2I4533.jpg',
      series: [49, 52, 55, 54, 57, 59, 61, 64, 66, 69, 73, 78]
    },
    'OUD Dunes': {
      occupancy: '92%', revenue: 'SAR 3.1M', area: '22,900 sqm', units: 67, approvals: 1, type: 'Mixed use', location: 'Riyadh, Saudi Arabia', image: 'assets/oud-homepage/https___images.squarespace-cdn.com_content_v1_68f677afba491e524acf3912_1760982980534-RR6EXDT3GQQ7UOLSPEWI_OUD_Dunes0010.jpg',
      series: [70, 72, 75, 74, 77, 80, 82, 84, 86, 88, 90, 92]
    }
  };
  const panel = chart.closest('.performance-card');
  const controls = document.createElement('div'); controls.className = 'chart-periods';
  [3, 6, 9, 12].forEach((months) => { const button = document.createElement('button'); button.type = 'button'; button.textContent = `${months} months`; button.dataset.months = months; controls.append(button); });
  panel.querySelector('.panel-title').append(controls);
  function draw(months) {
    const values = propertyData[activeProperty].series.slice(-months);
    const current = { labels: labels.slice(-months), values };
    const width = 1000; const height = 300; const points = current.values.map((value, index) => `${(index / (current.values.length - 1 || 1)) * width},${height - value * 2.4}`).join(' ');
    chart.innerHTML = `<svg viewBox="0 0 ${width} ${height}" preserveAspectRatio="none" role="img" aria-label="Occupancy trend"><polyline class="chart-grid-line" points="0,60 ${width},60"></polyline><polyline class="chart-grid-line" points="0,120 ${width},120"></polyline><polyline class="chart-grid-line" points="0,180 ${width},180"></polyline><polyline class="chart-grid-line" points="0,240 ${width},240"></polyline><polyline class="chart-line-svg" points="${points}"></polyline>${current.values.map((value, index) => `<circle class="chart-point-svg" cx="${(index / (current.values.length - 1 || 1)) * width}" cy="${height - value * 2.4}" r="6"></circle>`).join('')}</svg><div class="chart-labels">${current.labels.map((label) => `<span>${label}</span>`).join('')}</div>`;
    controls.querySelectorAll('button').forEach((button) => button.classList.toggle('active', Number(button.dataset.months) === months));
  }
  controls.addEventListener('click', (event) => { const button = event.target.closest('button'); if (button) { currentMonths = Number(button.dataset.months); draw(currentMonths); } });
  draw(12);
  if (select) select.dispatchEvent(new Event('change'));
  const reportsViewAll = document.querySelector('#reports .panel-title a'); if (reportsViewAll) reportsViewAll.href = 'landlord_reports.html';
  const approvalsViewAll = document.querySelector('#approvals .panel-title a'); if (approvalsViewAll) approvalsViewAll.href = 'landlord_approvals.html';
  const propertyDetailsLink = document.querySelector('.property-card .panel-title a'); if (propertyDetailsLink) propertyDetailsLink.href = 'landlord_properties.html';
  document.querySelectorAll('#reports .report-list > a').forEach((row) => { const view = document.createElement('button'); view.type = 'button'; view.className = 'report-view'; view.textContent = 'View'; view.onclick = () => { window.location.href = 'landlord_report_detail.html'; }; row.append(view); });
  document.querySelectorAll('#approvals .approval-list > a').forEach((row) => { const approve = document.createElement('button'); approve.type = 'button'; approve.className = 'approval-approve'; approve.textContent = 'Approve'; approve.onclick = () => { window.location.href = `landlord_approval_detail.html?request=${encodeURIComponent(row.querySelector('strong').textContent)}`; }; row.append(approve); });
})();
