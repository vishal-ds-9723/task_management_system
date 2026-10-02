    let allEvents = [];
    let selectedDate = null;
    let activeStatusFilter = 'all';
    let activeTypeFilter = 'all';
    let activePlatformFilter = 'all';
    let activePriorityFilter = 'all';
    let viewMode = 'normal';
    let activeRange = 'today';
    let rangeStatusFilter = null; // normal, overdue, today-status

    function getFilteredEvents() {
        let events = allEvents;
        if (activeStatusFilter !== 'all') {
            events = events.filter(e => {
                const s = ((e.extendedProps||{}).status||'todo').replace(' ','').replace('_','');
                return s === activeStatusFilter;
            });
        }
        if (activeTypeFilter !== 'all') {
            events = events.filter(e => (e.extendedProps||{}).type === activeTypeFilter);
        }
        if (activePlatformFilter !== 'all') {
            events = events.filter(e => (e.extendedProps||{}).platform === activePlatformFilter);
        }
        if (activePriorityFilter !== 'all') {
            events = events.filter(e => (e.extendedProps||{}).priority === activePriorityFilter);
        }
        return events;
    }

    function hasActiveAdvFilters() {
        return activeTypeFilter !== 'all' || activePlatformFilter !== 'all' || activePriorityFilter !== 'all' || activeStatusFilter !== 'all' || viewMode !== 'normal';
    }

    function updateClearBtn() {
        const active = hasActiveAdvFilters();
        document.getElementById('clearFiltersBtn').style.display = active ? 'inline-flex' : 'none';
        // Update badge on Filters toggle
        const count = (activeTypeFilter !== 'all' ? 1 : 0) + (activePlatformFilter !== 'all' ? 1 : 0) + (activePriorityFilter !== 'all' ? 1 : 0);
        const badge = document.getElementById('activeFilterBadge');
        if (count > 0) { badge.textContent = count; badge.style.display = 'flex'; }
        else { badge.style.display = 'none'; }
    }

    function updateFilterCounts() {
        const base = allEvents.filter(e => {
            let pass = true;
            if (activeTypeFilter !== 'all') pass = pass && (e.extendedProps||{}).type === activeTypeFilter;
            if (activePlatformFilter !== 'all') pass = pass && (e.extendedProps||{}).platform === activePlatformFilter;
            if (activePriorityFilter !== 'all') pass = pass && (e.extendedProps||{}).priority === activePriorityFilter;
            return pass;
        });
        const todo = base.filter(e=>((e.extendedProps||{}).status||'todo')==='todo').length;
        const inp = base.filter(e=>((e.extendedProps||{}).status||'todo')==='inprogress').length;
        const rev = base.filter(e=>((e.extendedProps||{}).status||'todo')==='review').length;
        const comp = base.filter(e=>((e.extendedProps||{}).status||'todo')==='completed').length;
        document.getElementById('filterTodoCount').textContent = todo;
        document.getElementById('filterInprogressCount').textContent = inp;
        document.getElementById('filterReviewCount').textContent = rev;
        document.getElementById('filterCompletedCount').textContent = comp;
        document.getElementById('sbTodoCount').textContent = todo;
        document.getElementById('sbInprogressCount').textContent = inp;
        document.getElementById('sbReviewCount').textContent = rev;
        document.getElementById('sbCompletedCount').textContent = comp;

        // Overdue count
        const todayStr = toIsoDateLocal(new Date());
        const overdue = allEvents.filter(e => {
            const p = e.extendedProps||{};
            const s = (p.status||'todo').replace(' ','').replace('_','');
            if (s === 'completed') return false;
            if (p.dateType !== 'deadline') return false;
            const d = p.deadline;
            return d && d < todayStr;
        });
        document.getElementById('overdueCount').textContent = overdue.length;

        updateTodayActivity();
        updateClearBtn();
    }

    function toggleStatusFilter(btn) {
        const status = btn.dataset.status;
        viewMode = 'normal';
        if (status === activeStatusFilter && status !== 'all') {
            activeStatusFilter = 'all';
        } else {
            activeStatusFilter = status;
        }
        document.querySelectorAll('.csf-pill').forEach(p => p.classList.remove('active'));
        if (activeStatusFilter === 'all') {
            document.querySelector('.csf-pill.csf-all').classList.add('active');
        } else {
            document.querySelector(`.csf-pill[data-status="${activeStatusFilter}"]`).classList.add('active');
        }
        updateFilterCounts();
        renderTasksList();
        if (typeof rebuildDotsFiltered === 'function') rebuildDotsFiltered();
    }

    function toggleAdvFilter(filterType, btn) {
        viewMode = 'normal';
        const val = btn.dataset[filterType];
        let groupId, activeVar;
        if (filterType === 'type') {
            if (val === activeTypeFilter && val !== 'all') { activeTypeFilter = 'all'; } else { activeTypeFilter = val; }
            groupId = 'typeFilterGroup';
        } else if (filterType === 'platform') {
            if (val === activePlatformFilter && val !== 'all') { activePlatformFilter = 'all'; } else { activePlatformFilter = val; }
            groupId = 'platformFilterGroup';
        } else if (filterType === 'priority') {
            if (val === activePriorityFilter && val !== 'all') { activePriorityFilter = 'all'; } else { activePriorityFilter = val; }
            groupId = 'priorityFilterGroup';
        }

        const group = document.getElementById(groupId);
        group.querySelectorAll('.af-pill').forEach(p => p.classList.remove('active'));
        const currentVal = filterType === 'type' ? activeTypeFilter : filterType === 'platform' ? activePlatformFilter : activePriorityFilter;
        if (currentVal === 'all') {
            group.querySelector('[data-' + filterType + '="all"]').classList.add('active');
        } else {
            group.querySelector('[data-' + filterType + '="' + currentVal + '"]').classList.add('active');
        }

        updateFilterCounts();
        renderTasksList();
        if (typeof rebuildDotsFiltered === 'function') rebuildDotsFiltered();
    }

    function toggleAdvPanel() {
        const panel = document.getElementById('advFilterPanel');
        const btn = document.getElementById('advFilterToggle');
        if (panel.style.display === 'none') {
            panel.style.display = 'flex';
            btn.classList.add('open');
        } else {
            panel.style.display = 'none';
            btn.classList.remove('open');
        }
    }

    function clearAllFilters() {
        activeStatusFilter = 'all';
        activeTypeFilter = 'all';
        activePlatformFilter = 'all';
        activePriorityFilter = 'all';
        viewMode = 'normal';
        rangeStatusFilter = null;

        document.querySelectorAll('.csf-pill').forEach(p => p.classList.remove('active'));
        document.querySelector('.csf-pill.csf-all').classList.add('active');
        document.querySelectorAll('.adv-filter-group').forEach(g => {
            g.querySelectorAll('.af-pill').forEach(p => p.classList.remove('active'));
            g.querySelector('.af-pill:first-child').classList.add('active');
        });
        document.getElementById('rangeFilterActive').style.display = 'none';
        document.querySelectorAll('.rs-stat').forEach(s => s.classList.remove('active-range-filter'));

        updateFilterCounts();
        renderTasksList();
        if (typeof rebuildDotsFiltered === 'function') rebuildDotsFiltered();
    }

    function jumpToToday() {
        viewMode = 'normal';
        const todayStr = toIsoDateLocal(new Date());
        selectedDate = todayStr;
        activeStatusFilter = 'all';
        document.querySelectorAll('.csf-pill').forEach(p => p.classList.remove('active'));
        const allPill = document.querySelector('.csf-pill.csf-all');
        if (allPill) allPill.classList.add('active');
        switchRange('today');
        updateFilterCounts();
        renderTasksList();

        if (window._calendarInstance) {
            window._calendarInstance.gotoDate(todayStr);
            window._calendarInstance.today();
        }

        setSelectedDayVisual();

        const todayRange = document.querySelector(`.rt-date-group[data-range-date="${todayStr}"]`);
        if (todayRange) {
            todayRange.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            todayRange.style.animation = 'none';
            todayRange.offsetHeight;
            todayRange.style.animation = 'highlightPulse 0.8s ease';
        }
    }

    function showOverdueTasks() {
        viewMode = 'overdue';
        updateClearBtn();
        renderTasksList();
    }

    function filterTodayByStatus(status) {
        // Use the range status filter instead
        filterRangeByStatus(status);
    }

    function updateTodayActivity() {
        renderRangeView();
    }

    function renderTodayTasksList() { /* no-op — replaced by renderRangeView */ }

    function getDateRange(rangeKey) {
        const today = new Date(); today.setHours(0,0,0,0);
        const start = toIsoDateLocal(today);
        let end = start;
        let label = 'Today';
        let icon = '<i class="fas fa-bolt"></i>';
        if (rangeKey === 'today') {
            label = 'Today'; icon = '<i class="fas fa-bolt"></i>';
        } else if (rangeKey === '2') {
            const d = new Date(today); d.setDate(d.getDate()+1);
            end = toIsoDateLocal(d);
            label = 'Next 2 Days'; icon = '<i class="fas fa-calendar-day"></i>';
        } else if (rangeKey === '3') {
            const d = new Date(today); d.setDate(d.getDate()+2);
            end = toIsoDateLocal(d);
            label = 'Next 3 Days'; icon = '<i class="fas fa-calendar"></i>';
        } else if (rangeKey === '5') {
            const d = new Date(today); d.setDate(d.getDate()+4);
            end = toIsoDateLocal(d);
            label = 'Next 5 Days'; icon = '<i class="fas fa-calendar-week"></i>';
        } else if (rangeKey === '15') {
            const d = new Date(today); d.setDate(d.getDate()+14);
            end = toIsoDateLocal(d);
            label = 'Next 15 Days'; icon = '<i class="fas fa-clipboard-list"></i>';
        } else if (rangeKey === '30') {
            const d = new Date(today); d.setDate(d.getDate()+29);
            end = toIsoDateLocal(d);
            label = 'This Month'; icon = '<i class="fas fa-chart-bar"></i>';
        }
        return { start, end, label, icon };
    }

    function switchRange(rangeKey) {
        activeRange = rangeKey;
        rangeStatusFilter = null;
        // Update active tab
        document.querySelectorAll('.range-tab').forEach(t => t.classList.remove('active'));
        const tab = document.querySelector(`.range-tab[data-range="${rangeKey}"]`);
        if (tab) tab.classList.add('active');
        // Clear range status filter UI
        document.getElementById('rangeFilterActive').style.display = 'none';
        document.querySelectorAll('.rs-stat').forEach(s => s.classList.remove('active-range-filter'));
        renderRangeView();
    }

    function filterRangeByStatus(status) {
        if (rangeStatusFilter === status) {
            rangeStatusFilter = null;
            document.getElementById('rangeFilterActive').style.display = 'none';
            document.querySelectorAll('.rs-stat').forEach(s => s.classList.remove('active-range-filter'));
        } else {
            rangeStatusFilter = status;
            const label = statusLabels[status] || status;
            document.getElementById('rangeFilterLabel').innerHTML = `<i class="fa-solid fa-filter"></i> Showing: ${escapeHtml(label)} tasks`;
            document.getElementById('rangeFilterActive').style.display = 'flex';
            document.querySelectorAll('.rs-stat').forEach(s => s.classList.remove('active-range-filter'));
            const statMap = { todo:'rs-stat-todo', inprogress:'rs-stat-inp', review:'rs-stat-rev', completed:'rs-stat-done' };
            const el = document.querySelector(`.${statMap[status]}`);
            if (el) el.classList.add('active-range-filter');
        }
        renderRangeView();
    }

    function clearRangeStatusFilter() {
        rangeStatusFilter = null;
        document.getElementById('rangeFilterActive').style.display = 'none';
        document.querySelectorAll('.rs-stat').forEach(s => s.classList.remove('active-range-filter'));
        renderRangeView();
    }

    function closeDateSection() {
        document.getElementById('dateClickSection').style.display = 'none';
        selectedDate = null;
        setSelectedDayVisual();
    }

    function renderRangeView() {
        const { start, end, label, icon } = getDateRange(activeRange);
        const todayStr = toIsoDateLocal(new Date());

        // Get events in range
        let rangeEvents = allEvents.filter(e => {
            const d = toIsoDateLocal(e.start);
            return d >= start && d <= end;
        });

        // Apply global advanced filters
        if (activeTypeFilter !== 'all') rangeEvents = rangeEvents.filter(e => (e.extendedProps||{}).type === activeTypeFilter);
        if (activePlatformFilter !== 'all') rangeEvents = rangeEvents.filter(e => (e.extendedProps||{}).platform === activePlatformFilter);
        if (activePriorityFilter !== 'all') rangeEvents = rangeEvents.filter(e => (e.extendedProps||{}).priority === activePriorityFilter);

        // Status counts (before status filter)
        const todo = rangeEvents.filter(e=>((e.extendedProps||{}).status||'todo')==='todo');
        const inp = rangeEvents.filter(e=>((e.extendedProps||{}).status||'todo')==='inprogress');
        const rev = rangeEvents.filter(e=>((e.extendedProps||{}).status||'todo')==='review');
        const comp = rangeEvents.filter(e=>((e.extendedProps||{}).status||'todo')==='completed');
        const total = rangeEvents.length;

        // Update header
        document.getElementById('rsIcon').innerHTML = icon;
        document.getElementById('rsTitle').textContent = label;
        document.getElementById('rsTotalBadge').textContent = total;

        // Date range display
        if (activeRange === 'today') {
            const d = new Date(); d.setHours(0,0,0,0);
            document.getElementById('rsDateRange').textContent = d.toLocaleDateString('en-US', { weekday:'long', month:'short', day:'numeric' });
        } else {
            const startD = new Date(`${start}T00:00:00`);
            const endD = new Date(`${end}T00:00:00`);
            const fmt = { month:'short', day:'numeric' };
            document.getElementById('rsDateRange').textContent = startD.toLocaleDateString('en-US', fmt) + ' — ' + endD.toLocaleDateString('en-US', fmt);
        }

        // Status counts + bars
        document.getElementById('rsTodoCount').textContent = todo.length;
        document.getElementById('rsInpCount').textContent = inp.length;
        document.getElementById('rsRevCount').textContent = rev.length;
        document.getElementById('rsDoneCount').textContent = comp.length;

        const maxCount = Math.max(todo.length, inp.length, rev.length, comp.length, 1);
        document.getElementById('rsTodoBar').style.width = (todo.length / maxCount * 100) + '%';
        document.getElementById('rsInpBar').style.width = (inp.length / maxCount * 100) + '%';
        document.getElementById('rsRevBar').style.width = (rev.length / maxCount * 100) + '%';
        document.getElementById('rsDoneBar').style.width = (comp.length / maxCount * 100) + '%';

        // Meta row
        const deadlines = rangeEvents.filter(e=>(e.extendedProps||{}).dateType==='deadline').length;
        const posts = rangeEvents.filter(e=>(e.extendedProps||{}).dateType==='post_date').length;
        document.getElementById('rsDeadlines').textContent = deadlines;
        document.getElementById('rsPosts').textContent = posts;

        // Overdue
        const overdue = allEvents.filter(e => {
            const p = e.extendedProps||{};
            const s = (p.status||'todo').replace(' ','').replace('_','');
            if (s === 'completed') return false;
            if (p.dateType !== 'deadline') return false;
            return p.deadline && p.deadline < todayStr;
        });
        document.getElementById('rsOverdue').textContent = overdue.length;
        document.getElementById('rsOverdueWrap').style.display = overdue.length > 0 ? 'inline-flex' : 'none';

        // Upcoming (tasks after current range)
        const nextDay = new Date(`${end}T00:00:00`);
        nextDay.setDate(nextDay.getDate()+1);
        const nextEnd = new Date(nextDay); nextEnd.setDate(nextEnd.getDate()+3);
        const upcoming = allEvents.filter(e => {
            const ds = toIsoDateLocal(e.start);
            return ds > end && ds <= toIsoDateLocal(nextEnd);
        });
        document.getElementById('rsUpcoming').textContent = upcoming.length;

        // Apply rangeStatusFilter for task list
        let displayEvents = rangeEvents;
        if (rangeStatusFilter) {
            displayEvents = rangeEvents.filter(e => {
                const s = ((e.extendedProps||{}).status||'todo').replace(' ','').replace('_','');
                return s === rangeStatusFilter;
            });
        }

        // Group by date
        const grouped = {};
        displayEvents.forEach(evt => {
            const d = toIsoDateLocal(evt.start);
            if (!grouped[d]) grouped[d] = [];
            grouped[d].push(evt);
        });

        // Build all dates in range (including empty)
        const allDates = [];
        let cur = new Date(`${start}T00:00:00`);
        const endDate = new Date(`${end}T00:00:00`);
        while (cur <= endDate) {
            allDates.push(toIsoDateLocal(cur));
            cur.setDate(cur.getDate() + 1);
        }

        const container = document.getElementById('rangeTasksList');

        if (displayEvents.length === 0) {
            container.innerHTML = `<div class="rt-no-tasks">${rangeStatusFilter ? 'No ' + (statusLabels[rangeStatusFilter]||rangeStatusFilter).toLowerCase() + ' tasks in this period' : 'No tasks in this period'}</div>`;
            return;
        }

        let html = '';
        allDates.forEach(dateStr => {
            const tasks = grouped[dateStr] || [];
            const isToday = dateStr === todayStr;
            const dateObj = new Date(`${dateStr}T00:00:00`);
            const dayName = dateObj.toLocaleDateString('en-US', { weekday:'short' });
            const dateFull = dateObj.toLocaleDateString('en-US', { month:'short', day:'numeric' });

            html += `<div class="rt-date-group ${isToday ? 'is-today' : ''}" data-range-date="${dateStr}">
                <div class="rt-date-header ${isToday ? 'today-header' : ''}">
                    <span class="rt-date-day">${escapeHtml(dayName)}</span>
                    <span class="rt-date-full">${escapeHtml(dateFull)}</span>
                    ${isToday ? '<span class="rt-date-today-tag">Today</span>' : ''}
                    ${tasks.length > 0 ? `<span class="rt-date-count">${tasks.length}</span>` : ''}
                </div>`;

            if (tasks.length === 0) {
                html += `<div class="rt-empty-day">— No tasks —</div>`;
            } else {
                tasks.forEach(evt => {
                    html += buildTaskRowHtml(evt, false);
                });
            }
            html += '</div>';
        });

        container.innerHTML = html;
    }

    function buildMiniTaskRow(evt) {
        const p = evt.extendedProps || {};
        const status = p.status||'todo';
        const type = p.type||'post';
        const dateType = p.dateType||'deadline';
        return `<div class="task-row" data-event-idx="${evt._idx}" data-datetype="${escapeHtml(dateType)}" style="padding:6px 9px;margin-bottom:4px;">
            <div class="tr-top" style="margin-bottom:3px">
                <div class="tr-title" style="font-size:11px">${escapeHtml(evt.title)}</div>
            </div>
            <div style="display:flex;align-items:center;gap:4px;font-size:10px;color:var(--text3)">
                <span class="tr-badge tr-badge-type type-${escapeHtml(type)}" style="font-size:9px;padding:1px 5px">${typeEmoji[type]||'<i class="fas fa-pen-fancy"></i>'} ${escapeHtml(type)}</span>
                <span class="task-status ${escapeHtml(status)}" style="font-size:8px;padding:1px 5px">${escapeHtml(status)}</span>
                <span style="margin-left:auto;color:${p.clientColor||'var(--text3)'};font-size:9px">${escapeHtml(p.clientEmoji||'')} ${escapeHtml(p.client||'')}</span>
            </div>
        </div>`;
    }

    const typeEmoji = { reel:'<i class="fas fa-film"></i>', post:'<i class="fas fa-pen-fancy"></i>', story:'<i class="fas fa-mobile-alt"></i>', video:'<i class="fas fa-video"></i>', carousel:'<i class="fas fa-images"></i>' };
    const platformEmoji = { instagram:'<i class="fab fa-instagram"></i>', facebook:'<i class="fab fa-facebook"></i>', youtube:'<i class="fab fa-youtube"></i>', twitter:'<i class="fab fa-x-twitter"></i>', linkedin:'<i class="fab fa-linkedin"></i>', tiktok:'<i class="fab fa-tiktok"></i>' };
    const priorityConfig = { urgent:{ dot:'tr-priority-urgent', label:'<i class="fas fa-circle" style="font-size:8px;color:#EF4444"></i> Urgent', color:'#EF4444' }, high:{ dot:'tr-priority-high', label:'<i class="fas fa-circle" style="font-size:8px;color:#F97316"></i> High', color:'#F97316' }, normal:{ dot:'', label:'Normal', color:'var(--text3)' }, low:{ dot:'', label:'Low', color:'var(--text3)' } };

    function toIsoDateLocal(dateLike) {
        const d = new Date(dateLike);
        return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    }

    function escapeHtml(str) {
        return String(str ?? '').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#039;');
    }

    function formatDateLabel(isoDate) {
        const d = new Date(`${isoDate}T00:00:00`);
        const today = new Date(); today.setHours(0,0,0,0);
        const tom = new Date(today); tom.setDate(tom.getDate()+1);
        const yest = new Date(today); yest.setDate(yest.getDate()-1);
        const cd = new Date(`${isoDate}T00:00:00`); cd.setHours(0,0,0,0);
        if (cd.getTime()===today.getTime()) return '<i class="fas fa-map-marker-alt" style="font-size:10px"></i> Today';
        if (cd.getTime()===tom.getTime()) return 'Tomorrow';
        if (cd.getTime()===yest.getTime()) return 'Yesterday';
        return d.toLocaleDateString('en-US',{weekday:'short',month:'short',day:'numeric'});
    }

    function formatDate(iso) {
        if (!iso) return null;
        const d = new Date(`${iso}T00:00:00`);
        return d.toLocaleDateString('en-US',{month:'short',day:'numeric',year:'numeric'});
    }

    function setSelectedDayVisual() {
        document.querySelectorAll('#calendarView .fc-daygrid-day').forEach(cell => {
            cell.classList.toggle('fc-day-selected', cell.getAttribute('data-date')===selectedDate);
        });
    }

    function buildTaskRowHtml(evt, showDate) {
        const p = evt.extendedProps || {};
        const status = p.status||'todo';
        const type = p.type||'post';
        const priority = p.priority||'normal';
        const dateType = p.dateType||'deadline';
        const prCfg = priorityConfig[priority]||priorityConfig.normal;
        const dateStr = showDate ? `<span class="tr-date">${formatDate(toIsoDateLocal(evt.start))}</span>` : '';

        // Check if overdue
        const todayStr = toIsoDateLocal(new Date());
        const isOverdue = dateType === 'deadline' && p.deadline && p.deadline < todayStr && status !== 'completed';
        const overdueTag = isOverdue ? `<span class="tr-badge" style="background:#EF444415;color:#EF4444;border:1px solid #EF444430;font-size:9px"><i class="fas fa-exclamation-triangle" style="font-size:9px"></i> Overdue</span>` : '';

        return `
            <button type="button" class="task-row" data-event-idx="${evt._idx}" data-datetype="${escapeHtml(dateType)}" ${isOverdue ? 'style="border-color:#EF444430"' : ''}>
                <div class="tr-top">
                    <div class="tr-title">${escapeHtml(evt.title)}</div>
                    ${prCfg.dot ? `<span class="tr-priority-dot ${prCfg.dot}" title="${escapeHtml(priority)}"></span>` : ''}
                </div>
                <div class="tr-badges">
                    <span class="tr-badge tr-badge-type type-${escapeHtml(type)}">${typeEmoji[type]||'<i class="fas fa-pen-fancy"></i>'} ${escapeHtml(type)}</span>
                    ${p.platform ? `<span class="tr-badge tr-badge-platform">${platformEmoji[p.platform]||'<i class="fas fa-globe"></i>'} ${escapeHtml(p.platform)}</span>` : ''}
                    <span class="tr-badge tr-badge-datetype ${dateType==='deadline'?'db-deadline':'db-post'}">${dateType==='deadline'?'<i class="fas fa-crosshairs"></i> Deadline':'<i class="fas fa-paper-plane"></i> Post Date'}</span>
                    ${overdueTag}
                    ${dateStr}
                </div>
                <div class="tr-bottom">
                    <span class="tr-client" style="color:${p.clientColor||'var(--text3)'}">
                        ${p.clientLogo ? `<img src="/storage/${p.clientLogo}" style="width:14px;height:14px;border-radius:3px;object-fit:cover;vertical-align:middle;margin-right:2px">` : (p.clientEmoji || '')}
                        ${escapeHtml(p.client||'N/A')}
                    </span>
                    <span style="display:flex;align-items:center;gap:6px">
                        ${p.assignee ? `<span class="tr-assignee"><i class="fas fa-user" style="font-size:10px;opacity:0.5"></i> ${escapeHtml(p.assignee)}</span>` : '<span style="color:#EF4444;font-weight:600"><i class="fas fa-exclamation-triangle" style="font-size:10px"></i> Unassigned</span>'}
                        <span class="task-status ${escapeHtml(status)}">${escapeHtml(status)}</span>
                        <span class="ask-review-btn" data-task-id="${p.taskId||''}" onclick="event.stopPropagation();askForReview(${p.taskId||0},'${escapeHtml(evt.title)}')" title="">
                            <i class="fa-solid fa-clipboard-check"></i>
                            <span class="ask-review-tooltip">Ask for Review</span>
                        </span>
                    </span>
                </div>
            </button>`;
    }

    const statusLabels = { todo:'To Do', inprogress:'In Progress', review:'In Review', completed:'Completed' };

    function renderTasksList() {
        const dateSection = document.getElementById('dateClickSection');
        const labelEl = document.getElementById('dcsDateTitle');
        const subEl = document.getElementById('dcsDateLabel');
        const countEl = document.getElementById('dcsCount');
        const panelEl = document.getElementById('tasksListPanel');
        const bdEl = document.getElementById('dateBreakdown');

        // Always update range view
        renderRangeView();

        // ── Overdue mode ──
        if (viewMode === 'overdue') {
            dateSection.style.display = 'block';
            const todayStr = toIsoDateLocal(new Date());
            const overdue = allEvents.filter(e => {
                const p = e.extendedProps||{};
                const s = (p.status||'todo').replace(' ','').replace('_','');
                if (s === 'completed') return false;
                if (p.dateType !== 'deadline') return false;
                const d = p.deadline;
                return d && d < todayStr;
            });

            let filtered = overdue;
            if (activeTypeFilter !== 'all') filtered = filtered.filter(e => (e.extendedProps||{}).type === activeTypeFilter);
            if (activePlatformFilter !== 'all') filtered = filtered.filter(e => (e.extendedProps||{}).platform === activePlatformFilter);
            if (activePriorityFilter !== 'all') filtered = filtered.filter(e => (e.extendedProps||{}).priority === activePriorityFilter);

            labelEl.textContent = 'Overdue Tasks';
            subEl.textContent = 'Past deadline';
            countEl.textContent = String(filtered.length);
            bdEl.style.display = 'none';

            if (filtered.length === 0) {
                panelEl.innerHTML = '<div class="empty-msg" style="border-color:#10B98140;background:#10B98108"><i class="fas fa-check-circle" style="color:#10B981;margin-right:6px"></i> No overdue tasks — great job!</div>';
                return;
            }

            filtered.sort((a, b) => ((a.extendedProps||{}).deadline||'').localeCompare((b.extendedProps||{}).deadline||''));

            let html = `<div class="overdue-panel-header">
                <span class="overdue-panel-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <span class="overdue-panel-text">Tasks past their deadline</span>
                <span class="overdue-panel-count">${filtered.length}</span>
            </div>`;

            const groups = { critical:[], high:[], moderate:[] };
            filtered.forEach(evt => {
                const p = evt.extendedProps||{};
                const dl = new Date(`${p.deadline}T00:00:00`);
                const today = new Date(); today.setHours(0,0,0,0);
                const diffDays = Math.floor((today - dl) / (1000*60*60*24));
                if (diffDays > 7) groups.critical.push(evt);
                else if (diffDays > 3) groups.high.push(evt);
                else groups.moderate.push(evt);
            });

            const overdueGroups = [
                { key:'critical', label:'<i class="fas fa-circle" style="font-size:8px;color:#EF4444"></i> Critical (7+ days overdue)', tasks:groups.critical },
                { key:'high', label:'<i class="fas fa-circle" style="font-size:8px;color:#F97316"></i> High (3-7 days overdue)', tasks:groups.high },
                { key:'moderate', label:'<i class="fas fa-circle" style="font-size:8px;color:#EAB308"></i> Moderate (1-3 days overdue)', tasks:groups.moderate },
            ];

            overdueGroups.forEach(g => {
                if (g.tasks.length === 0) return;
                html += `<div class="tr-date-group">
                    <div class="tr-date-header">${g.label} <span class="tr-date-header-count">${g.tasks.length}</span></div>`;
                g.tasks.forEach(evt => { html += buildTaskRowHtml(evt, true); });
                html += '</div>';
            });

            panelEl.innerHTML = html;
            setSelectedDayVisual();
            return;
        }

        // ── Filter mode: show ALL tasks matching the active status filter ──
        if (activeStatusFilter !== 'all') {
            dateSection.style.display = 'block';
            let filtered = allEvents.filter(e => {
                const s = ((e.extendedProps||{}).status||'todo').replace(' ','').replace('_','');
                return s === activeStatusFilter;
            });
            if (activeTypeFilter !== 'all') filtered = filtered.filter(e => (e.extendedProps||{}).type === activeTypeFilter);
            if (activePlatformFilter !== 'all') filtered = filtered.filter(e => (e.extendedProps||{}).platform === activePlatformFilter);
            if (activePriorityFilter !== 'all') filtered = filtered.filter(e => (e.extendedProps||{}).priority === activePriorityFilter);
            const label = statusLabels[activeStatusFilter] || activeStatusFilter;
            labelEl.textContent = label + ' Tasks';
            subEl.textContent = 'Filtered across all dates';
            countEl.textContent = String(filtered.length);
            bdEl.style.display = 'none';

            if (filtered.length === 0) {
                panelEl.innerHTML = `<div class="empty-msg">No <b>${escapeHtml(label)}</b> tasks found</div>`;
                setSelectedDayVisual();
                return;
            }

            const grouped = {};
            filtered.forEach(evt => {
                const d = toIsoDateLocal(evt.start);
                if (!grouped[d]) grouped[d] = [];
                grouped[d].push(evt);
            });
            const sortedDates = Object.keys(grouped).sort();

            let html = '';
            sortedDates.forEach(date => {
                html += `<div class="tr-date-group">
                    <div class="tr-date-header">${formatDateLabel(date)} <span class="tr-date-header-count">${grouped[date].length}</span></div>`;
                grouped[date].forEach(evt => {
                    html += buildTaskRowHtml(evt, false);
                });
                html += '</div>';
            });

            panelEl.innerHTML = html;
            setSelectedDayVisual();
            return;
        }

        // ── Date mode: show tasks for selected date ──
        if (!selectedDate) {
            dateSection.style.display = 'none';
            setSelectedDayVisual();
            return;
        }

        dateSection.style.display = 'block';
        let allDateTasks = allEvents.filter(e => toIsoDateLocal(e.start)===selectedDate);
        if (activeTypeFilter !== 'all') allDateTasks = allDateTasks.filter(e => (e.extendedProps||{}).type === activeTypeFilter);
        if (activePlatformFilter !== 'all') allDateTasks = allDateTasks.filter(e => (e.extendedProps||{}).platform === activePlatformFilter);
        if (activePriorityFilter !== 'all') allDateTasks = allDateTasks.filter(e => (e.extendedProps||{}).priority === activePriorityFilter);
labelEl.innerHTML = formatDateLabel(selectedDate);
        subEl.textContent = new Date(`${selectedDate}T00:00:00`).toLocaleDateString('en-US',{weekday:'long', month:'long', day:'numeric', year:'numeric'});
        countEl.textContent = String(allDateTasks.length);

        if (allDateTasks.length === 0) {
            bdEl.style.display = 'none';
            panelEl.innerHTML = '<div class="empty-msg">No tasks on this date</div>';
            setSelectedDayVisual();
            return;
        }

        const deadlines = allDateTasks.filter(e=>(e.extendedProps||{}).dateType==='deadline').length;
        const posts = allDateTasks.filter(e=>(e.extendedProps||{}).dateType==='post_date').length;
        document.getElementById('deadlineCount').innerHTML = `<i class="fa-solid fa-bullseye"></i> <b>${deadlines}</b> Deadline${deadlines!==1?'s':''}`;
        document.getElementById('postDateCount').innerHTML = `<i class="fa-solid fa-paper-plane"></i> <b>${posts}</b> Post${posts!==1?'s':''}`;
        bdEl.style.display = 'flex';

        panelEl.innerHTML = allDateTasks.map(evt => buildTaskRowHtml(evt, false)).join('');
        setSelectedDayVisual();
    }

    function showCalTaskModal(title, start, props) {
        const p = props || {};
        const type = p.type||'post';
        const status = p.status||'todo';
        const priority = p.priority||'normal';
        const prCfg = priorityConfig[priority]||priorityConfig.normal;
        const dateLabel = p.dateType==='post_date' ? 'Post Date' : 'Deadline';

        let html = `
            <div class="cm-header">
                <div class="cm-title">${escapeHtml(title||'Task Details')}</div>
                <div class="cm-badges">
                    <span class="cm-badge type-${escapeHtml(type)}">${typeEmoji[type]||'<i class="fas fa-pen-fancy"></i>'} ${escapeHtml(type)}</span>
                    ${p.platform ? `<span class="cm-badge tr-badge-platform">${platformEmoji[p.platform]||'<i class="fas fa-globe"></i>'} ${escapeHtml(p.platform)}</span>` : ''}
                    <span class="cm-badge task-status ${escapeHtml(status)}">${escapeHtml(status)}</span>
                    <span class="cm-badge" style="color:${prCfg.color};background:${prCfg.color}15;border:1px solid ${prCfg.color}30">${prCfg.label}</span>
                </div>
            </div>
            <div class="cm-body">
                <div class="cm-dates">
                    <div class="cm-date-card ${p.dateType==='deadline'?'active':''}">
                        <div class="cm-date-type"><i class="fas fa-crosshairs" style="margin-right:4px"></i> Deadline</div>
                        ${p.deadline ? `<div class="cm-date-val">${formatDate(p.deadline)}</div>` : '<div class="cm-date-none">Not set</div>'}
                    </div>
                    <div class="cm-date-card ${p.dateType==='post_date'?'active':''}">
                        <div class="cm-date-type"><i class="fas fa-paper-plane" style="margin-right:4px"></i> Post Date</div>
                        ${p.postDate ? `<div class="cm-date-val">${formatDate(p.postDate)}</div>` : '<div class="cm-date-none">Not set</div>'}
                    </div>
                </div>
                <div class="cm-grid">
                    <div class="cm-field">
                        <div class="cm-label">Client</div>
                        <div class="cm-value" style="color:${p.clientColor||'var(--text1)'};display:flex;align-items:center;gap:6px">
                            ${p.clientLogo ? `<img src="/storage/${p.clientLogo}" style="width:20px;height:20px;border-radius:4px;object-fit:cover">` : (p.clientEmoji || '🏢')}
                            <span>
                                ${escapeHtml(p.client||'N/A')}
                                ${p.clientCategory ? `<span style="font-size:11px;color:var(--text3);margin-left:4px">(${escapeHtml(p.clientCategory)})</span>` : ''}
                            </span>
                        </div>
                    </div>
                    <div class="cm-field">
                        <div class="cm-label">Assigned To</div>
                        <div class="cm-value">${p.assignee ? `<i class="fas fa-user" style="font-size:10px;opacity:0.5"></i> ${escapeHtml(p.assignee)}` : '<span style="color:#EF4444"><i class="fas fa-exclamation-triangle" style="font-size:10px"></i> Unassigned</span>'}</div>
                    </div>
                    <div class="cm-field">
                        <div class="cm-label">Created By</div>
                        <div class="cm-value">${p.creator ? escapeHtml(p.creator) : '—'}</div>
                    </div>
                    <div class="cm-field">
                        <div class="cm-label">Comments</div>
                        <div class="cm-value"><i class="fas fa-comments" style="font-size:11px;opacity:0.5"></i> ${p.commentsCount||0} comment${(p.commentsCount||0)!==1?'s':''}</div>
                    </div>`;

        if (p.description) {
            html += `
                    <div class="cm-field cm-field-full">
                        <div class="cm-label">Caption / Brief</div>
                        <div class="cm-value" style="white-space:pre-wrap;background:var(--card2);padding:8px 10px;border-radius:8px;border:1px solid var(--border);max-height:120px;overflow-y:auto">${escapeHtml(p.description)}</div>
                    </div>`;
        }

        const links = p.referenceLinks;
        if (links && Array.isArray(links) && links.length > 0) {
            const linksHtml = links.filter(l=>l).map(l => `<a href="${escapeHtml(l)}" target="_blank" rel="noopener noreferrer"><i class="fas fa-link" style="margin-right:4px"></i>${escapeHtml(l.length > 50 ? l.substring(0,50)+'...' : l)}</a>`).join('<br>');
            html += `
                    <div class="cm-field cm-field-full">
                        <div class="cm-label">Reference Links</div>
                        <div class="cm-value">${linksHtml}</div>
                    </div>`;
        }

        html += `
                </div>
            </div>
            <div class="cm-footer">
                <div class="cm-meta">Created ${escapeHtml(p.createdAt||'—')}</div>
                <div style="display:flex;gap:8px">
                    <a href="/strategist/tasks/${p.taskId}/edit" class="btn-sec" style="text-decoration:none;font-size:13px;padding:7px 14px"><i class="fas fa-pen"></i> Edit</a>
                    <a href="${p.viewUrl||'/strategist/tracking'}" class="btn-primary" style="text-decoration:none;font-size:13px;padding:7px 18px">Open Task →</a>
                </div>
            </div>`;

        document.getElementById('calTaskDetails').innerHTML = html;
        document.getElementById('calTaskModal').style.display = 'block';
    }

    function closeCalTaskModal() { document.getElementById('calTaskModal').style.display = 'none'; }

    function askForReview(taskId, taskTitle) {
        if (!taskId) return;
        if (!confirm('Ask for review on "' + taskTitle + '"?')) return;
        fetch('/api/tasks/' + taskId + '/ask-review', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(r => { if (!r.ok) throw new Error('Request failed'); return r.json(); })
        .then(data => { alert(data.message || 'Review requested successfully!'); })
        .catch(err => { console.error(err); alert('Could not send review request.'); });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendarView');
        const clientFilterEl = document.getElementById('clientFilter');
        const tasksListPanelEl = document.getElementById('tasksListPanel');
        const bannerEl = document.getElementById('clientBanner');

        if (!calendarEl) return;

        // Client filter change — show/hide banner
        function updateBanner() {
            const selectedOpt = document.querySelector('.ccs-option.selected');
            if (!clientFilterEl.value) { bannerEl.style.display = 'none'; return; }
            if (!selectedOpt) { bannerEl.style.display = 'none'; return; }
            
            const logo = selectedOpt.dataset.logo;
            const emoji = selectedOpt.dataset.emoji;
            const cbEmoji = document.getElementById('cbEmoji');
            
            if (logo) {
                cbEmoji.innerHTML = `<img src="/storage/${logo}" style="width:32px;height:32px;border-radius:6px;object-fit:cover">`;
            } else {
                cbEmoji.innerHTML = emoji || '🏢';
            }
            
            document.getElementById('cbName').textContent = selectedOpt.querySelector('.ccs-opt-name').textContent.trim();
            document.getElementById('cbCategory').textContent = selectedOpt.dataset.category || '';
            document.getElementById('cbTotal').textContent = selectedOpt.dataset.total || '0';
            document.getElementById('cbActive').textContent = selectedOpt.dataset.active || '0';
            document.getElementById('cbOverdue').textContent = selectedOpt.dataset.overdue || '0';
            bannerEl.style.display = 'flex';
        }

        function initCalendar() {
            const statusColors = { todo:'#FFA500', inprogress:'#3B82F6', in_progress:'#3B82F6', review:'#8B5CF6', completed:'#10B981' };

            // Tooltip element
            let tooltipEl = document.createElement('div');
            tooltipEl.className = 'cal-tooltip';
            document.body.appendChild(tooltipEl);
            let tooltipTimeout = null;

            function buildDayDots() {
                document.querySelectorAll('#calendarView .fc-daygrid-day').forEach(cell => {
                    const dateStr = cell.getAttribute('data-date');
                    if (!dateStr) return;
                    // Remove old dots
                    const oldDots = cell.querySelector('.cal-day-dots');
                    if (oldDots) oldDots.remove();

                    const dayTasks = getFilteredEvents().filter(e => toIsoDateLocal(e.start) === dateStr);
                    if (dayTasks.length === 0) return;

                    const dotsContainer = document.createElement('div');
                    dotsContainer.className = 'cal-day-dots';

                    const maxDots = 5;
                    const shown = dayTasks.slice(0, maxDots);
                    shown.forEach(evt => {
                        const p = evt.extendedProps || {};
                        const status = (p.status||'todo').replace(' ','');
                        const isDeadline = p.dateType === 'deadline';
                        const dot = document.createElement('span');
                        dot.className = 'cal-day-dot' + (isDeadline ? ' dot-deadline' : '');
                        dot.style.background = statusColors[status] || '#FFA500';
                        dotsContainer.appendChild(dot);
                    });

                    if (dayTasks.length > maxDots) {
                        const count = document.createElement('span');
                        count.className = 'cal-day-count';
                        count.textContent = '+' + (dayTasks.length - maxDots);
                        dotsContainer.appendChild(count);
                    }

                    const frame = cell.querySelector('.fc-daygrid-day-frame');
                    if (frame) frame.appendChild(dotsContainer);
                });
            }

            function showTooltip(cell) {
                const dateStr = cell.getAttribute('data-date');
                if (!dateStr) return;
                const dayTasks = getFilteredEvents().filter(e => toIsoDateLocal(e.start) === dateStr);

                let html = `<div class="cal-tooltip-date">${formatDateLabel(dateStr)}</div>`;
                if (dayTasks.length === 0) {
                    html += '<div class="cal-tooltip-empty">No tasks</div>';
                } else {
                    const maxShow = 5;
                    dayTasks.slice(0, maxShow).forEach(evt => {
                        const p = evt.extendedProps || {};
                        const status = (p.status||'todo').replace(' ','');
                        const color = statusColors[status] || '#FFA500';
                        const icon = p.dateType === 'deadline' ? '<i class="fas fa-crosshairs" style="font-size:9px"></i>' : '<i class="fas fa-paper-plane" style="font-size:9px"></i>';
                        html += `<div class="cal-tooltip-item">
                            <span class="cal-tooltip-dot" style="background:${color}"></span>
                            <span class="cal-tooltip-title">${icon} ${escapeHtml(evt.title)}</span>
                        </div>`;
                    });
                    if (dayTasks.length > maxShow) {
                        html += `<div class="cal-tooltip-item" style="color:var(--text3);font-style:italic">+${dayTasks.length - maxShow} more...</div>`;
                    }
                }

                tooltipEl.innerHTML = html;

                // Position near the cell
                const rect = cell.getBoundingClientRect();
                const tooltipWidth = 220;
                let left = rect.left + rect.width / 2 - tooltipWidth / 2;
                let top = rect.bottom + 6;

                // Keep within viewport
                if (left < 8) left = 8;
                if (left + tooltipWidth > window.innerWidth - 8) left = window.innerWidth - tooltipWidth - 8;
                if (top + 200 > window.innerHeight) top = rect.top - 10;

                tooltipEl.style.left = left + 'px';
                tooltipEl.style.top = top + window.scrollY + 'px';
                tooltipEl.style.width = tooltipWidth + 'px';
                tooltipEl.classList.add('visible');
            }

            function hideTooltip() {
                tooltipEl.classList.remove('visible');
            }

            // Attach hover events via delegation
            calendarEl.addEventListener('mouseenter', function(e) {
                const cell = e.target.closest('.fc-daygrid-day');
                if (!cell) return;
                clearTimeout(tooltipTimeout);
                tooltipTimeout = setTimeout(() => showTooltip(cell), 200);
            }, true);

            calendarEl.addEventListener('mouseleave', function(e) {
                const cell = e.target.closest('.fc-daygrid-day');
                if (!cell) return;
                clearTimeout(tooltipTimeout);
                hideTooltip();
            }, true);

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: { left:'prev,next today', center:'title', right:'dayGridMonth,dayGridWeek' },
                contentHeight: 'auto',
                fixedWeekCount: false,
                events: function(info, successCallback, failureCallback) {
                    const params = new URLSearchParams({
                        client_id: clientFilterEl.value||'',
                        start: info.startStr,
                        end: info.endStr
                    });
                    fetch('/api/calendar/events?'+params.toString(), {
                        headers:{ 'X-Requested-With':'XMLHttpRequest', 'Accept':'application/json' }
                    })
                    .then(r => { if(!r.ok) throw new Error('Network error'); return r.json(); })
                    .then(data => {
                        allEvents = data.map((evt,idx) => ({...evt, _idx:idx}));
                        if (!selectedDate) selectedDate = toIsoDateLocal(new Date());
                        updateFilterCounts();
                        renderTasksList();
                        successCallback(getFilteredEvents());
                        // Build dots after events are rendered
                        setTimeout(buildDayDots, 50);
                    })
                    .catch(err => { console.error('Calendar fetch error:',err); failureCallback(err); });
                },
                eventContent: function() {
                    // Return empty — we hide events via CSS and show dots instead
                    return { html: '' };
                },
                dateClick: function(info) {
                    selectedDate = info.dateStr;
                    viewMode = 'normal';
                    activeStatusFilter = 'all';
                    document.querySelectorAll('.csf-pill').forEach(p => p.classList.remove('active'));
                    document.querySelector('.csf-pill.csf-all').classList.add('active');
                    updateClearBtn();
                    renderTasksList();
                    hideTooltip();
                    // Scroll to date group in range if visible
                    const rangeGroup = document.querySelector(`.rt-date-group[data-range-date="${info.dateStr}"]`);
                    if (rangeGroup) {
                        rangeGroup.scrollIntoView({ behavior:'smooth', block:'nearest' });
                        rangeGroup.style.animation = 'none';
                        rangeGroup.offsetHeight;
                        rangeGroup.style.animation = 'highlightPulse 0.8s ease';
                    }
                },
                eventClick: function(info) {
                    selectedDate = toIsoDateLocal(info.event.start);
                    renderTasksList();
                    showCalTaskModal(info.event.title, info.event.start, info.event.extendedProps||{});
                    hideTooltip();
                },
                editable: true,
                eventDurationEditable: false,
                eventDrop: function(info) {
                    const p = info.event.extendedProps || {};
                    const dateType = p.dateType || 'deadline';
                    const newDate = toIsoDateLocal(info.event.start);
                    const taskId = p.taskId;

                    if (!taskId) { info.revert(); return; }
                    if (!confirm('Move ' + (dateType === 'post_date' ? 'post date' : 'deadline') + ' of "' + info.event.title + '" to ' + newDate + '?')) {
                        info.revert();
                        return;
                    }

                    fetch('/api/tasks/' + taskId + '/reschedule', {
                        method: 'PATCH',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({ date_type: dateType, new_date: newDate })
                    })
                    .then(r => { if (!r.ok) throw new Error('Failed'); return r.json(); })
                    .then(data => {
                        // Update local event data
                        if (dateType === 'deadline') {
                            info.event.setExtendedProp('deadline', newDate);
                        } else {
                            info.event.setExtendedProp('postDate', newDate);
                        }
                        calendar.refetchEvents();
                    })
                    .catch(err => { console.error(err); alert('Could not reschedule task.'); info.revert(); });
                },
                dayMaxEvents: false,
                datesSet: function() {
                    setSelectedDayVisual();
                    setTimeout(buildDayDots, 50);
                }
            });
            calendar.render();

            // Store calendar instance for external access
            window._calendarInstance = calendar;

            // Expose rebuild for filter changes
            window.rebuildDotsFiltered = function() {
                buildDayDots();
            };

            return calendar;
        }

        let calendar = initCalendar();

        // Custom dropdown functions — exposed globally
        window.toggleClientDropdown = function() {
            const sel = document.getElementById('ccsSelected');
            const dd = document.getElementById('ccsDropdown');
            const isOpen = dd.classList.contains('show');
            if (isOpen) {
                dd.classList.remove('show');
                sel.classList.remove('open');
            } else {
                dd.classList.add('show');
                sel.classList.add('open');
                document.getElementById('ccsSearch').value = '';
                filterClientOptions('');
                document.getElementById('ccsSearch').focus();
            }
        };

        window.selectClient = function(optionEl) {
            const value = optionEl.dataset.value;
            clientFilterEl.value = value;

            // Mark selected
            document.querySelectorAll('.ccs-option').forEach(o => o.classList.remove('selected'));
            optionEl.classList.add('selected');

            // Update display
            const sel = document.getElementById('ccsSelected');
            if (!value) {
                sel.innerHTML = '<span class="ccs-placeholder">All Clients</span><i class="fa-solid fa-chevron-down ccs-arrow"></i>';
            } else {
                const logoImg = optionEl.querySelector('.ccs-logo');
                const emojiSpan = optionEl.querySelector('.ccs-emoji');
                const name = optionEl.querySelector('.ccs-opt-name').textContent.trim();
                let iconHtml = '';
                if (logoImg) {
                    iconHtml = `<img src="${logoImg.src}" class="ccs-sel-logo">`;
                } else if (emojiSpan) {
                    iconHtml = `<span class="ccs-sel-emoji">${emojiSpan.textContent}</span>`;
                }
                sel.innerHTML = `${iconHtml}<span class="ccs-sel-name">${escapeHtml(name)}</span><i class="fa-solid fa-chevron-down ccs-arrow"></i>`;
            }

            // Close dropdown
            document.getElementById('ccsDropdown').classList.remove('show');
            sel.classList.remove('open');

            updateBanner();
            calendar.refetchEvents();
        };

        function filterClientOptions(query) {
            const q = query.toLowerCase();
            document.querySelectorAll('.ccs-option').forEach(opt => {
                const name = (opt.querySelector('.ccs-opt-name')?.textContent || '').toLowerCase();
                opt.style.display = name.includes(q) ? 'flex' : 'none';
            });
        }

        document.getElementById('ccsSearch').addEventListener('input', function() {
            filterClientOptions(this.value);
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.custom-client-select')) {
                document.getElementById('ccsDropdown').classList.remove('show');
                document.getElementById('ccsSelected').classList.remove('open');
            }
        });

        tasksListPanelEl.addEventListener('click', function(e) {
            const row = e.target.closest('[data-event-idx]');
            if (!row) return;
            const idx = Number(row.getAttribute('data-event-idx'));
            const evt = allEvents.find(i => i._idx===idx);
            if (!evt) return;
            showCalTaskModal(evt.title, evt.start, evt.extendedProps||{});
        });

        // Today's tasks list click handler (now ranges)
        document.getElementById('rangeTasksList').addEventListener('click', function(e) {
            const row = e.target.closest('[data-event-idx]');
            if (!row) return;
            const idx = Number(row.getAttribute('data-event-idx'));
            const evt = allEvents.find(i => i._idx===idx);
            if (!evt) return;
            showCalTaskModal(evt.title, evt.start, evt.extendedProps||{});
        });

        document.getElementById('calTaskModal').addEventListener('click', function(e) {
            if (e.target===this) closeCalTaskModal();
        });
    });
