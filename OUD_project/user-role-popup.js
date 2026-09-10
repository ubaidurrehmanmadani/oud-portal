(() => {
 const role=document.body.dataset.portalRole;
 const info={
 landlord:{title:'Landlord',can:['View only your assigned properties, including shared properties, and switch between them.','View property details, tenants, photos, occupancy, revenue and performance charts.','View approved monthly, quarterly and annual reports and download property documents, contracts, invoices and certificates.','Approve or reject property requests, with comments and supporting documents.','Receive notifications for new reports and approval requests; access retained monthly reports.'],cannot:['Access properties that are not assigned to you, other landlords’ private information, or employee departments.','Create users, change roles or permissions, or manage system settings.','Enter or edit the financial figures behind charts. These are provided by the Heads of Property Management and Hospitality Management.','Publish reports or see reports awaiting Admin approval.','See the identity of the person who uploaded property photos.']},
 employee:{title:'Employee',can:['View your department dashboard, documents and announcements.','Search and download your department documents and training materials.','Access downloadable Oud Academy training and receive new-document notifications.'],cannot:['Upload, replace or delete documents.','Access other departments or landlord/property information.','Manage users, permissions, reports or system settings.']},
 manager:{title:'Department Manager',can:['Manage documents and training within your assigned department.','Post department announcements as described in the manager panel requirements.','Manage department users only when explicitly permitted by Admin.','Heads of Property Management and Hospitality Management provide monthly property figures for Admin review.'],cannot:['Access other departments or general landlord/property data.','Change system-wide settings or override Admin permissions.','Create or delete users without Admin permission.','Publish reports to landlords before Admin approval.'],note:'The proposal differs on manager scope: its finalized section says upload-only, while earlier sections include document management and announcements. User administration requires explicit Admin permission.'},
 admin:{title:'Admin · CEO & MD',can:['Manage users, invitations, password resets and role permissions.','Create, rename or archive departments and properties.','Manage documents, reports and visibility across the platform.','Send announcements and review department and property views.','Approve reports for landlord publication. CEO and MD use this Admin role.'],cannot:['Enter landlord occupancy, rent and revenue figures under the proposal: this responsibility belongs to the Heads of Property Management and Hospitality Management.','Discard the permanent monthly report record.']}
 };
 if(!info[role])return;
 const data=info[role];
 let trigger=document.querySelector('.oud-user-role');
 const button=document.createElement('button'); button.type='button'; button.className='oud-user-role';
 button.innerHTML='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg><span>User Role</span>';
 if(trigger)trigger.replaceWith(button);else document.body.append(button);
 const dialog=document.createElement('dialog');dialog.id='oud-role-dialog';dialog.setAttribute('aria-labelledby','oud-role-title');
 dialog.innerHTML='<header class="oud-role-header"><h2 id="oud-role-title"></h2><button type="button" class="oud-role-close" aria-label="Close user role">×</button></header>';
 dialog.querySelector('h2').textContent=data.title;
 for(const [label,key] of [['What you can do','can'],['What you cannot do','cannot']]){
 const section=document.createElement('section'),heading=document.createElement('h3'),list=document.createElement('ul');heading.textContent=label;
 data[key].forEach(text=>{const li=document.createElement('li');li.textContent=text;list.append(li);});section.append(heading,list);dialog.append(section);
 }
 if(data.note){const p=document.createElement('p');p.textContent=data.note;dialog.append(p);}
 const note=document.createElement('p');note.textContent='Role guide based on the OUD Portal Proposal. Permissions describe the intended system; this HTML preview does not enforce authentication or access controls.';dialog.append(note);
 document.body.append(dialog);button.setAttribute('aria-haspopup','dialog');button.setAttribute('aria-controls',dialog.id);
 button.addEventListener('click',()=>dialog.showModal());
 dialog.querySelector('.oud-role-close').addEventListener('click',()=>dialog.close());
 dialog.addEventListener('click',e=>{const r=dialog.getBoundingClientRect();if(e.target===dialog&&(e.clientX<r.left||e.clientX>r.right||e.clientY<r.top||e.clientY>r.bottom))dialog.close();});
 dialog.addEventListener('close',()=>button.focus());
})();
