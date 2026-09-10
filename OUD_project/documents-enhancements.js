(function () {
  const section = document.querySelector('.document-table');
  if (!section) return;
  const descriptions = [
    'Reference guide for daily hospitality operations, service routines, safety checks, and team procedures used across the department.',
    'Guidelines for creating consistent, welcoming guest experiences while maintaining the service standards expected across every guest interaction.',
    'A practical template for clear and complete shift handovers, including key updates, outstanding tasks, and important operational notes.'
  ];
  section.querySelectorAll('.document-description').forEach((item, index) => { item.textContent = descriptions[index]; });
  [
    ['Safety', 'Workplace Health & Safety Guide', 'Essential guidance for maintaining a safe workplace, reporting concerns, and following daily health and safety responsibilities.', 'PDF · Updated 2 weeks ago'],
    ['People', 'Employee Welcome Guide', 'A practical introduction to workplace expectations, team communication, employee support, and the values that shape OUD.', 'PDF · Updated 3 weeks ago'],
    ['Operations', 'Guest Services Checklist', 'A daily checklist to help teams prepare spaces, coordinate service details, and deliver a consistent guest experience.', 'DOCX · Updated 1 month ago']
  ].forEach(([category, title, description, meta]) => {
    const row = document.createElement('article'); row.className = 'card module-card';
    row.innerHTML = `<span class="card-kicker">${category}</span><h3>${title}</h3><p class="document-description">${description}</p><p class="muted">${meta}</p><a href="#">Download</a>`;
    section.append(row);
  });
})();
