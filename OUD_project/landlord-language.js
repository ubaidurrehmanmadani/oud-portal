(function () {
  const actions = document.querySelector('.topbar-actions');
  if (!actions || actions.querySelector('.portal-language')) return;
  const switcher = document.createElement('div');
  switcher.className = 'portal-language';
  switcher.innerHTML = '<button type="button" data-landlord-lang="en">English</button><button type="button" data-landlord-lang="ar">العربية</button>';
  const profile = actions.querySelector('.portal-profile');
  actions.insertBefore(switcher, profile ? profile.nextSibling : actions.firstChild);
  const translations = {
    'Property workspace': 'مساحة العقارات', Dashboard: 'لوحة التحكم', Properties: 'العقارات', Reports: 'التقارير', Documents: 'المستندات', Approvals: 'الموافقات',
    'Oud Compass | Landlord portal': 'بوصلة عود | بوابة المالك', 'Property dashboard': 'لوحة العقارات', Landlord: 'مالك العقار', Property: 'العقار', 'Sign out': 'تسجيل الخروج',
    'Track property performance, review reports, and respond to requests from one private workspace.': 'تابع أداء العقارات وراجع التقارير واستجب للطلبات من مساحة عمل خاصة واحدة.', 'Reporting period': 'الفترة المالية', 'Awaiting monthly review': 'بانتظار المراجعة الشهرية',
    Occupancy: 'نسبة الإشغال', 'Net revenue': 'صافي الإيرادات', 'Leased area': 'المساحة المؤجرة', 'Open approvals': 'الموافقات المفتوحة', 'Requires your review': 'تتطلب مراجعتك', 'Performance overview': 'نظرة عامة على الأداء', 'Property performance': 'أداء العقار', 'Last 12 months': 'آخر 12 شهراً', Occupancy: 'الإشغال', 'Revenue trend': 'اتجاه الإيرادات',
    'Property details': 'تفاصيل العقار', 'View details': 'عرض التفاصيل', 'Property type': 'نوع العقار', 'Mixed use': 'متعدد الاستخدامات', Location: 'الموقع', 'Total units': 'إجمالي الوحدات', units: 'وحدات', 'Your access': 'صلاحيتك', 'Owner access': 'صلاحية المالك', 'Property photos': 'صور العقار', Reports: 'التقارير', 'View all': 'عرض الكل', Download: 'تنزيل', 'Action required': 'إجراء مطلوب', 'Approval requests': 'طلبات الموافقة', Review: 'مراجعة',
    'Monthly performance report': 'تقرير الأداء الشهري', 'Property budget review': 'مراجعة ميزانية العقار', 'Maintenance summary': 'ملخص الصيانة', 'Lobby maintenance budget': 'ميزانية صيانة الردهة', 'Tenant fit-out request': 'طلب تجهيز المستأجر', 'Special event approval': 'موافقة فعالية خاصة',
    'Submitted 2 days ago': 'تم الإرسال قبل يومين', 'Submitted 5 days ago': 'تم الإرسال قبل 5 أيام', 'Submitted 1 week ago': 'تم الإرسال قبل أسبوع', 'August 2026 · PDF': 'أغسطس 2026 · PDF', 'Q3 2026 · PDF': 'الربع الثالث 2026 · PDF',
    'OUD Reserve': 'محمية عود', 'OUD Square': 'ساحة عود', 'OUD Dunes': 'كثبان عود', 'Riyadh, Saudi Arabia': 'الرياض، المملكة العربية السعودية', 'Last 12 months': 'آخر 12 شهراً'
  };
  const originals = new WeakMap();
  const apply = (language) => {
    const arabic = language === 'ar';
    localStorage.setItem('oud-lang', language);
    document.documentElement.lang = language;
    document.documentElement.dir = arabic ? 'rtl' : 'ltr';
    document.body.classList.toggle('rtl', arabic);
    switcher.querySelectorAll('button').forEach((button) => button.classList.toggle('active', button.dataset.landlordLang === language));
    document.querySelectorAll('body *:not(script):not(style)').forEach((element) => {
      if (element.children.length) return;
      if (!originals.has(element)) originals.set(element, element.textContent.trim());
      const original = originals.get(element);
      element.textContent = arabic ? (translations[original] || original) : original;
    });
    if (profile) profile.innerHTML = arabic ? '<strong>سعيد القحطاني</strong><span>مالك العقار · landlord@oud.sa</span>' : '<strong>Saeed ALKHATANI</strong><span>Landlord · landlord@oud.sa</span>';
  };
  switcher.querySelectorAll('button').forEach((button) => button.addEventListener('click', () => apply(button.dataset.landlordLang)));
  apply(localStorage.getItem('oud-lang') === 'ar' ? 'ar' : 'en');
})();
