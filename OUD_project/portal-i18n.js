(function () {
  const map = {
    Workspace: 'مساحة العمل', Dashboard: 'لوحة التحكم', Documents: 'المستندات', 'Oud Academy': 'أكاديمية عود', Announcements: 'الإعلانات', Search: 'البحث',
    'Your workspace': 'مساحة عملك', 'Welcome back.': 'مرحباً بعودتك.', 'Department library': 'مكتبة القسم', 'Department documents.': 'مستندات القسم.', 'Private library': 'المكتبة الخاصة', 'Find content': 'البحث عن المحتوى', 'Search your workspace.': 'ابحث في مساحة عملك.', 'Department updates': 'تحديثات القسم', 'Stay informed.': 'ابقَ على اطلاع.',
    'Access training files for your role, health, safety, and everyday operations.': 'اطّلع على ملفات التدريب الخاصة بدورك والصحة والسلامة والعمليات اليومية.', 'View and download documents shared with your department.': 'اعرض المستندات التي تمت مشاركتها مع قسمك وقم بتنزيلها.', 'Search across documents, training materials, and department announcements.': 'ابحث في المستندات ومواد التدريب وإعلانات القسم.', 'Messages and updates from your department manager and OUD Academy.': 'رسائل وتحديثات من مدير قسمك وأكاديمية عود.',
    'Training library': 'مكتبة التدريب', 'Keep learning.': 'واصل التعلم.', 'Required': 'إلزامي', Hospitality: 'الضيافة', Reference: 'مرجع', Operations: 'العمليات', Policies: 'السياسات', Templates: 'النماذج', Result: 'نتيجة',
    'Workplace health & safety': 'الصحة والسلامة في مكان العمل', 'Guest experience essentials': 'أساسيات تجربة الضيوف', 'Emergency response guide': 'دليل الاستجابة للطوارئ', 'Service excellence foundations': 'أساسيات التميز في الخدمة', 'Saudi hospitality standards': 'معايير الضيافة السعودية', 'Daily operations briefing': 'إحاطة العمليات اليومية',
    'Learn the essential safety practices for a confident, prepared workplace.': 'تعلّم ممارسات السلامة الأساسية لبيئة عمل واثقة ومستعدة.', 'Build memorable guest experiences through calm, thoughtful service.': 'اصنع تجارب مميزة للضيوف من خلال خدمة هادئة ومدروسة.', 'Know what to do, who to contact, and how to respond when it matters.': 'اعرف ما يجب فعله ومن تتواصل معه وكيف تستجيب عند الحاجة.', 'Practice the habits that make every guest interaction feel considered.': 'تدرّب على العادات التي تجعل كل تفاعل مع الضيف مدروساً.', 'Review the standards that shape welcoming Saudi hospitality.': 'راجع المعايير التي تشكل الضيافة السعودية الترحيبية.', 'Start each shift with the information your team needs.': 'ابدأ كل وردية بالمعلومات التي يحتاجها فريقك.',
    'Sign out': 'تسجيل الخروج', 'Search documents and announcements': 'ابحث في المستندات والإعلانات', 'Open result →': 'فتح النتيجة ←', 'Download document →': 'تنزيل المستند ←', 'Open training →': 'فتح التدريب ←', 'Download guide →': 'تنزيل الدليل ←', 'View training →': 'عرض التدريب ←', 'Read updates →': 'قراءة التحديثات ←', 'Open library →': 'فتح المكتبة ←',
    'Latest announcements': 'أحدث الإعلانات', 'Recent activity': 'النشاط الأخير', 'Quick access': 'وصول سريع', 'View all': 'عرض الكل', 'Today': 'اليوم', Yesterday: 'أمس', '4 days ago': 'قبل 4 أيام', '3 days ago': 'قبل 3 أيام', '1 week ago': 'قبل أسبوع',
    'Oud Academy': 'أكاديمية عود', 'Training video': 'فيديو تدريبي', 'Select a video card to begin your training session.': 'اختر بطاقة فيديو لبدء جلسة التدريب.', 'Close video': 'إغلاق الفيديو', 'Video': 'فيديو', 'Hospitality Management': 'إدارة الضيافة', 'Hospitality Management · employee@oud.sa': 'إدارة الضيافة · employee@oud.sa', 'Search your workspace': 'ابحث في مساحة عملك', 'Search your workspace.': 'ابحث في مساحة عملك.',
    'Hospitality Operations Handbook': 'دليل عمليات الضيافة', 'Guest Experience Standards': 'معايير تجربة الضيوف', 'Daily Shift Handover': 'تسليم الوردية اليومية', 'PDF · Updated today': 'ملف PDF · تم التحديث اليوم', 'PDF · Updated 4 days ago': 'ملف PDF · تم التحديث قبل 4 أيام', 'DOCX · Updated 1 week ago': 'ملف DOCX · تم التحديث قبل أسبوع', 'Download latest document': 'تنزيل أحدث مستند', 'Open training materials': 'فتح مواد التدريب', 'Search department content': 'البحث في محتوى القسم', 'Try “safety” or “handover”': 'جرّب "السلامة" أو "التسليم"', 'Training · Oud Academy': 'تدريب · أكاديمية عود', 'Document · Hospitality Management': 'مستند · إدارة الضيافة', 'Your department library, training materials, and latest announcements in one calm workspace.': 'مكتبة قسمك ومواد التدريب وأحدث الإعلانات في مساحة عمل هادئة واحدة.', 'Department files': 'ملفات القسم', 'Training items': 'مواد التدريب', 'Announcements': 'الإعلانات', 'Profile status': 'حالة الملف الشخصي', 'Available to view and download': 'متاح للعرض والتنزيل', 'Oud Academy materials': 'مواد أكاديمية عود', 'Latest department updates': 'أحدث تحديثات القسم', 'Your access is active': 'صلاحيتك مفعلة', 'Department library': 'مكتبة القسم', 'Continue with health, safety, and role-based training.': 'تابع التدريب على الصحة والسلامة والتدريب حسب الدور.', 'Browse the latest files shared with Hospitality Management.': 'تصفح أحدث الملفات المشتركة مع إدارة الضيافة.', 'Stay current with messages from your department manager.': 'تابع أحدث رسائل مدير قسمك.', 'New Hospitality Operations Handbook uploaded': 'تم رفع دليل عمليات الضيافة الجديد', 'Health and safety refresher assigned': 'تم تعيين تدريب تنشيطي للصحة والسلامة', 'Department announcement published': 'تم نشر إعلان القسم', '3 days ago': 'منذ 3 أيام'
  };

  Object.assign(map, {
    'Your department library, training materials, and latest announcements in one calm workspace.': 'مكتبة قسمك ومواد التدريب وأحدث الإعلانات في مساحة عمل هادئة واحدة.',
    'Available to view and download': 'متاح للعرض والتنزيل', 'Oud Academy materials': 'مواد أكاديمية عود', 'Continue with health, safety, and role-based training.': 'تابع التدريب على الصحة والسلامة والتدريب حسب الدور.',
    'Browse the latest files shared with Hospitality Management.': 'تصفح أحدث الملفات المشتركة مع إدارة الضيافة.', 'Stay current with messages from your department manager.': 'تابع أحدث رسائل مدير قسمك.',
    'New Hospitality Operations Handbook uploaded': 'تم رفع دليل عمليات الضيافة الجديد', 'Health and safety refresher assigned': 'تم تعيين تدريب تنشيطي للصحة والسلامة', 'Department announcement published': 'تم نشر إعلان القسم'
    , 'Reference guide for daily hospitality operations and team procedures.': 'دليل مرجعي للعمليات اليومية وإجراءات فريق الضيافة.', 'Guidelines for creating consistent and welcoming guest experiences.': 'إرشادات لإنشاء تجارب ضيوف متسقة ومرحبة.', 'A practical template for clear and complete shift handovers.': 'نموذج عملي لتسليم ورديات واضح ومتكامل.'
  });
  const originals = new WeakMap();
  function apply(language) {
    const arabic = language === 'ar';
    document.documentElement.lang = arabic ? 'ar' : 'en';
    document.documentElement.dir = arabic ? 'rtl' : 'ltr';
    document.querySelectorAll('body *').forEach((element) => {
      if (element.children.length) return;
      if (!originals.has(element)) originals.set(element, element.textContent.trim());
      const original = originals.get(element);
      if (arabic && map[original]) element.textContent = map[original];
      else if (!arabic) element.textContent = original;
    });
    const profile = document.querySelector('.portal-profile');
    if (profile) profile.innerHTML = `<strong>${arabic ? 'أحمد العود' : 'Ahmed Al Oud'}</strong><span>${arabic ? 'إدارة الضيافة · employee@oud.sa' : 'Hospitality Management · employee@oud.sa'}</span>`;
    const search = document.querySelector('.header-search input');
    if (search) { search.placeholder = arabic ? 'ابحث في مساحة عملك' : 'Search your workspace'; search.setAttribute('aria-label', search.placeholder); }
    document.querySelectorAll('[data-video-badge]').forEach((badge) => { if (arabic) badge.textContent = badge.textContent.replace(/Video/g, 'فيديو'); });
  }
  window.addEventListener('oud-language-change', (event) => apply(event.detail));
  document.addEventListener('click', (event) => { const button = event.target.closest('[data-portal-lang]'); if (button) setTimeout(() => apply(button.dataset.portalLang), 0); });
  window.setTimeout(() => apply(localStorage.getItem('oud-lang') === 'ar' ? 'ar' : 'en'), 0);
})();
