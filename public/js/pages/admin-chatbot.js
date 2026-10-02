(function(){
    const chatbot    = document.getElementById('adminChatbot');
    let currentTab   = 'overview';
    let searchTimer  = null;
    let activeFilter = '';
    let activeFilterLabel = '';
    let proactiveDone = false;

    /* ── Wizard State ── */
    const WIZARD_ORDER = ['ask_title','ask_client','ask_type','ask_platform','ask_assignee','ask_postdate','ask_priority','confirm'];
    const WIZARD_LABELS = {
        ask_title:'Task Title', ask_client:'Client', ask_type:'Task Type',
        ask_platform:'Platform', ask_assignee:'Assignee', ask_postdate:'Post Date',
        ask_priority:'Priority', confirm:'Confirm'
    };
    const WIZARD_TOTAL = 7;

    let wizardStep = 'idle';
    const initialWizardData = () => ({
        title:'', client_id:'', type:'post', platform:'instagram', assigned_to:'',
        priority:'normal', deadline:'', post_date:'',
        _dateInfo:'', _dateInfoColor:'',
        _typeLabel:'Post', _platformLabel:'Instagram', _clientLabel:'', _assigneeLabel:'',
        status:'todo'
    });
    let wizardData = initialWizardData();

    // Wizard data — fetched lazily on first wizard start, then cached.
    let clientsData = [];
    let usersData   = [];
    let wizardDataLoaded = false;
    let wizardDataLoading = null;

    function ensureWizardData(){
        if (wizardDataLoaded) return Promise.resolve();
        if (wizardDataLoading) return wizardDataLoading;
        wizardDataLoading = fetch('/api/chatbot/wizard-data', {
            headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
        })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(d => {
            clientsData = d.clients || [];
            usersData   = d.users   || [];
            wizardDataLoaded = true;
        })
        .finally(() => { wizardDataLoading = null; });
        return wizardDataLoading;
    }

    // Cancel previous tab fetch when switching/searching
    let tabAbortController = null;

    // Track whether overview has been loaded once (so we don't refetch on every open)
    let overviewLoaded = false;

    /* ── Open / Close ── */
    window.acbToggle = function(){
        const wasOpen = chatbot.classList.contains('open');
        chatbot.classList.toggle('open');

        // First time opening — load overview stats (used to be inlined into HTML).
        if (!wasOpen && !overviewLoaded) {
            overviewLoaded = true;
            loadTabData('overview');
        }

        if (!wasOpen && !proactiveDone) {
            proactiveDone = true;
            if (currentTab === 'assistant') {
                setTimeout(showProactiveGreeting, 600);
            }
        }
    };

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && chatbot.classList.contains('open')) chatbot.classList.remove('open');
    });

    /* ── Proactive greeting on first open ── */
    function showProactiveGreeting(){
        const overdue    = parseInt(document.getElementById('valOverdue')?.textContent)    || 0;
        const unassigned = parseInt(document.getElementById('valUnassigned')?.textContent) || 0;

        let html = '';
        if (overdue > 0 || unassigned > 0) {
            const parts = [];
            if (overdue    > 0) parts.push(`<b style="color:#ef4444">${overdue} overdue</b>`);
            if (unassigned > 0) parts.push(`<b style="color:#eab308">${unassigned} unassigned</b>`);
            html = `<div style="margin-bottom:8px">⚠️ Heads up — you have ${parts.join(' and ')} task${(overdue+unassigned)!==1?'s':''}.</div>`;
            html += `<div class="acb-options-grid">`;
            if (overdue > 0) html += `
                <div class="acb-option-box" onclick="acbQuickCmd('show overdue')">
                    <div class="acb-opt-icon" style="color:#ef4444;background:rgba(239,68,68,.1)"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div class="acb-opt-info"><div class="acb-opt-name">Overdue</div><div class="acb-opt-sub">${overdue} task${overdue!==1?'s':''}</div></div>
                </div>`;
            if (unassigned > 0) html += `
                <div class="acb-option-box" onclick="acbQuickCmd('show unassigned')">
                    <div class="acb-opt-icon" style="color:#eab308;background:rgba(234,179,8,.1)"><i class="fa-solid fa-user-slash"></i></div>
                    <div class="acb-opt-info"><div class="acb-opt-name">Unassigned</div><div class="acb-opt-sub">${unassigned} task${unassigned!==1?'s':''}</div></div>
                </div>`;
            html += `
                <div class="acb-option-box" onclick="acbQuickCmd('create task')">
                    <div class="acb-opt-icon" style="color:var(--primary);background:rgba(var(--primary-rgb),.1)"><i class="fa-solid fa-plus"></i></div>
                    <div class="acb-opt-info"><div class="acb-opt-name">New Task</div><div class="acb-opt-sub">Quick wizard</div></div>
                </div>
            </div>`;
        } else {
            html = `✅ Everything looks great — no overdue or unassigned tasks! Type <b>"help"</b> to see what I can do.`;
        }
        showTyping(500, () => addChatMsg('bot', '', html));
    }

    /* ── Quick Create from overview ── */
    window.acbQuickCreateTask = function(){
        acbSwitchTab('assistant');
        setTimeout(startWizard, 250);
    };

    /* ── Drill-down from stat card ── */
    window.acbDrillDown = function(targetTab, filter, label){
        activeFilter      = filter;
        activeFilterLabel = label;
        switchPane(targetTab);
        const filterBar = document.getElementById('acbFilterBar');
        if (targetTab === 'tasks') {
            filterBar.style.display = 'flex';
            document.getElementById('acbFilterLabel').textContent = label;
        } else {
            filterBar.style.display = 'none';
        }
        clearSearchInput('task');
        clearSearchInput('client');
        loadTabData(targetTab);
    };

    window.acbGoBack = function(){
        activeFilter = ''; activeFilterLabel = '';
        document.getElementById('acbFilterBar').style.display = 'none';
        clearSearchInput('task');
        switchPane('overview');
        loadTabData('overview');
    };

    window.acbClearFilter = function(){
        activeFilter = ''; activeFilterLabel = '';
        document.getElementById('acbFilterBar').style.display = 'none';
        clearSearchInput('task');
        loadTabData('tasks');
    };

    window.acbClearSearch = function(type){
        clearSearchInput(type);
        loadTabData(type === 'task' ? 'tasks' : 'clients');
    };

    function clearSearchInput(type){
        const input = document.getElementById(type === 'task' ? 'acbTaskSearch' : 'acbClientSearch');
        const btn   = document.getElementById(type === 'task' ? 'acbTaskSearchClear' : 'acbClientSearchClear');
        if (input) input.value = '';
        if (btn)   btn.style.display = 'none';
    }

    /* ── Tab switching ── */
    window.acbSwitchTab = function(tab){
        if (tab === currentTab) return;
        activeFilter = ''; activeFilterLabel = '';
        document.getElementById('acbFilterBar').style.display = 'none';
        switchPane(tab);
        loadTabData(tab);
        if (tab === 'assistant' && !proactiveDone) {
            proactiveDone = true;
            setTimeout(showProactiveGreeting, 600);
        }
    };

    function switchPane(tab){
        currentTab = tab;
        document.querySelectorAll('.acb-tab').forEach(b => b.classList.remove('active'));
        document.querySelector(`.acb-tab[data-tab="${tab}"]`)?.classList.add('active');
        document.querySelectorAll('.acb-pane').forEach(p => p.classList.remove('active'));
        const map = {overview:'acbOverview',assistant:'acbAssistant',tasks:'acbTasks',clients:'acbClients',reports:'acbReports'};
        document.getElementById(map[tab])?.classList.add('active');
        document.getElementById('acbContent')?.scrollTo({top:0});
    }

    window.acbRefresh = function(){
        const icon = document.querySelector('.acb-header-btn i.fa-arrows-rotate');
        if (icon){ icon.classList.add('fa-spin'); setTimeout(() => icon.classList.remove('fa-spin'), 800); }
        loadTabData(currentTab);
    };

    window.acbSearchTasks = function(){
        const btn = document.getElementById('acbTaskSearchClear');
        const val = document.getElementById('acbTaskSearch')?.value?.trim();
        if (btn) btn.style.display = val ? 'flex' : 'none';
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadTabData('tasks'), 300);
    };

    window.acbSearchClients = function(){
        const btn = document.getElementById('acbClientSearchClear');
        const val = document.getElementById('acbClientSearch')?.value?.trim();
        if (btn) btn.style.display = val ? 'flex' : 'none';
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadTabData('clients'), 300);
    };

    /* ── Data loading ── */
    function loadTabData(tab){
        if (tab === 'assistant') return;

        // Cancel any in-flight tab request — protects against stale renders
        // when the user clicks tabs/types in the search box rapidly.
        if (tabAbortController) tabAbortController.abort();
        tabAbortController = new AbortController();
        const signal = tabAbortController.signal;

        document.getElementById('acbLoading').classList.add('show');
        let url = `/api/chatbot/tab-data?tab=${encodeURIComponent(tab)}`;
        if (tab === 'tasks'){
            if (activeFilter) url += `&status=${encodeURIComponent(activeFilter)}`;
            const s = document.getElementById('acbTaskSearch')?.value?.trim();
            if (s) url += `&search=${encodeURIComponent(s)}`;
        }
        if (tab === 'clients'){
            const s = document.getElementById('acbClientSearch')?.value?.trim();
            if (s) url += `&search=${encodeURIComponent(s)}`;
        }
        fetch(url, {signal, headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}})
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(data => {
                document.getElementById('acbLoading').classList.remove('show');
                if (tab === 'overview') renderOverview(data);
                else if (tab === 'tasks')   renderTasks(data);
                else if (tab === 'clients') renderClients(data);
                else if (tab === 'reports') renderReports(data);
            })
            .catch(err => {
                // Silently ignore aborted requests — they were intentionally cancelled.
                if (err && err.name === 'AbortError') return;
                document.getElementById('acbLoading').classList.remove('show');
            });
    }

    /* ── Chat ── */
    window.acbSendChat = function(){
        const input = document.getElementById('acbChatInput');
        const msg   = input.value.trim();
        if (!msg) return;
        addChatMsg('user', msg);
        input.value = '';
        processChatCommand(msg);
    };

    function addChatMsg(role, text, html){
        const history = document.getElementById('acbChatHistory');
        const div     = document.createElement('div');
        div.className = `acb-chat-msg ${role}`;
        div.innerHTML = `<div class="acb-msg-bubble">${html || esc(text)}</div>`;
        history.appendChild(div);
        history.scrollTop = history.scrollHeight;
    }

    function showTyping(delay, callback){
        const history = document.getElementById('acbChatHistory');
        const typing  = document.createElement('div');
        typing.className = 'acb-chat-msg bot acb-typing-indicator';
        typing.innerHTML = '<div class="acb-msg-bubble"><div class="acb-typing-dots"><span></span><span></span><span></span></div></div>';
        history.appendChild(typing);
        history.scrollTop = history.scrollHeight;
        setTimeout(() => { typing.remove(); callback && callback(); }, delay);
    }

    /* ── Expanded keyword command processor ── */
    function processChatCommand(msg){
        const m = msg.toLowerCase().trim();

        if (wizardStep !== 'idle'){
            handleWizardStep(msg);
            return;
        }

        // Create task
        if (/create\s*task|new\s*task|add\s*task|assign\s*task|make\s*task/.test(m)){
            showTyping(400, startWizard);
            return;
        }

        // Overdue
        if (/overdue|past\s*due|late\s*task|delayed/.test(m)){
            showTyping(350, () => { addChatMsg('bot','Showing overdue tasks...'); setTimeout(() => acbDrillDown('tasks','overdue','Overdue Tasks'), 200); });
            return;
        }

        // Unassigned
        if (/unassigned|no\s*assign|without\s*assign/.test(m)){
            showTyping(350, () => { addChatMsg('bot','Showing unassigned tasks...'); setTimeout(() => acbDrillDown('tasks','unassigned','Unassigned Tasks'), 200); });
            return;
        }

        // Today
        if (/\btoday\b|due\s*today|deadline\s*today/.test(m)){
            showTyping(350, () => { addChatMsg('bot','Showing tasks due today...'); setTimeout(() => acbDrillDown('tasks','today','Due Today'), 200); });
            return;
        }

        // Status filters
        if (/in\s*progress|inprogress|\bworking\b/.test(m)){
            showTyping(300, () => { addChatMsg('bot','Showing in-progress tasks...'); setTimeout(() => acbDrillDown('tasks','inprogress','In Progress'), 200); }); return;
        }
        if (/\breview\b|pending\s*review/.test(m)){
            showTyping(300, () => { addChatMsg('bot','Showing tasks pending review...'); setTimeout(() => acbDrillDown('tasks','review','Pending Review'), 200); }); return;
        }
        if (/\btodo\b|to[\s-]do/.test(m)){
            showTyping(300, () => { addChatMsg('bot','Showing to-do tasks...'); setTimeout(() => acbDrillDown('tasks','todo','Todo Tasks'), 200); }); return;
        }
        if (/\bcompleted\b|\bdone\b|\bfinished\b/.test(m)){
            showTyping(300, () => { addChatMsg('bot','Showing completed tasks...'); setTimeout(() => acbDrillDown('tasks','completed','Completed Tasks'), 200); }); return;
        }
        if (/\bpublished\b|\blive\b/.test(m)){
            showTyping(300, () => { addChatMsg('bot','Showing published tasks...'); setTimeout(() => acbDrillDown('tasks','published','Published Tasks'), 200); }); return;
        }

        // Tab navigation
        if (/\btask(s)?\b/.test(m) && !/create|new|add/.test(m)){
            showTyping(300, () => acbSwitchTab('tasks')); return;
        }
        if (/\bclient(s)?\b/.test(m)){
            showTyping(300, () => acbSwitchTab('clients')); return;
        }
        if (/\breport(s)?\b|\bstat(s|istic)?\b|\banalytics\b/.test(m)){
            showTyping(300, () => acbSwitchTab('reports')); return;
        }
        if (/\boverview\b|\bsummary\b|\bdashboard\b|\bhome\b/.test(m)){
            showTyping(300, () => acbSwitchTab('overview')); return;
        }

        // Count queries
        if (/how\s*many\s*task|total\s*task|task\s*count/.test(m)){
            const total   = document.getElementById('valTotalTasks')?.textContent || '?';
            const overdue = document.getElementById('valOverdue')?.textContent    || '?';
            showTyping(500, () => addChatMsg('bot', '', `There are <b>${total}</b> tasks total, with <b>${overdue}</b> overdue.`));
            return;
        }
        if (/how\s*many\s*client|total\s*client|client\s*count/.test(m)){
            const clients = document.getElementById('valClients')?.textContent || '?';
            showTyping(500, () => addChatMsg('bot', '', `There are <b>${clients}</b> active clients.`));
            return;
        }

        // Refresh
        if (/\brefresh\b|\breload\b|\bsync\b|\bupdate\s*data\b/.test(m)){
            addChatMsg('bot','Refreshing data...'); acbRefresh(); return;
        }

        // Greetings
        if (/^(hello|hi|hey|howdy|good\s*(morning|afternoon|evening))[!?.\s]*$/.test(m)){
            showTyping(500, () => {
                addChatMsg('bot', `Hi there! I'm Sarah, your admin assistant.`);
                setTimeout(() => showHelpCommands(), 300);
            });
            return;
        }

        // Help
        if (/\bhelp\b|\bcommand(s)?\b|\bwhat\s*can/.test(m)){
            showTyping(400, showHelpCommands); return;
        }

        // Fallback
        showTyping(600, () => {
            addChatMsg('bot', `I didn't catch that. Here's what I can do:`);
            setTimeout(showHelpCommands, 200);
        });
    }

    function showHelpCommands(){
        const cmds = [
            {icon:'fa-plus',       color:'var(--primary)', cmd:'create task',     label:'Create Task',      sub:'Step-by-step wizard'},
            {icon:'fa-fire',       color:'#ef4444',        cmd:'show overdue',    label:'Overdue Tasks',    sub:'Past deadline'},
            {icon:'fa-user-slash', color:'#eab308',        cmd:'show unassigned', label:'Unassigned Tasks', sub:'Needs assignment'},
            {icon:'fa-calendar-day',color:'#3b82f6',       cmd:'today',           label:'Due Today',        sub:"Today's deadlines"},
            {icon:'fa-building',   color:'#8b5cf6',        cmd:'show clients',    label:'Browse Clients',   sub:'Client list'},
            {icon:'fa-chart-bar',  color:'#10b981',        cmd:'show reports',    label:'View Reports',     sub:'Task analytics'},
        ];
        let html = `<div style="font-weight:700;margin-bottom:8px">Here's what I understand:</div>
        <div class="acb-options-grid">`;
        cmds.forEach(c => {
            html += `<div class="acb-option-box" onclick="acbQuickCmd('${c.cmd}')">
                <div class="acb-opt-icon" style="color:${c.color};background:${c.color.startsWith('#') ? c.color+'1a' : 'rgba(var(--primary-rgb),.1)'}"><i class="fa-solid ${c.icon}"></i></div>
                <div class="acb-opt-info"><div class="acb-opt-name">${c.label}</div><div class="acb-opt-sub">${c.sub}</div></div>
            </div>`;
        });
        html += `</div>`;
        addChatMsg('bot','',html);
    }

    window.acbQuickCmd = function(cmd){
        const m = cmd.toLowerCase();
        if      (m === 'show tasks'     || m === 'tasks')    acbSwitchTab('tasks');
        else if (m === 'show clients'   || m === 'clients')  acbSwitchTab('clients');
        else if (m === 'show reports'   || m === 'reports')  acbSwitchTab('reports');
        else if (m === 'overview')                           acbSwitchTab('overview');
        else if (m === 'refresh')                            acbRefresh();
        else if (m === 'show overdue'   || m === 'overdue')  acbDrillDown('tasks','overdue','Overdue Tasks');
        else if (m === 'show unassigned'|| m === 'unassigned') acbDrillDown('tasks','unassigned','Unassigned Tasks');
        else if (m === 'today')                              acbDrillDown('tasks','today','Due Today');
        else { addChatMsg('user', cmd); processChatCommand(cmd); }
    };

    /* ── Wizard ── */
    window.startWizard = function(isRestart){
        if (isRestart) document.getElementById('acbChatHistory').innerHTML = '<div class="acb-chat-msg bot"><div class="acb-msg-bubble">Restarting wizard...</div></div>';
        wizardStep = 'ask_title';
        wizardData = initialWizardData();
        updateWizardBar();
        let html = `<div>Let's create a new task. What is the <b>Task Title</b>?</div>`;
        html += `<div style="display:flex;gap:8px;margin-top:8px">
            <button class="acb-view-all" style="flex:1;margin:0;background:var(--bg,#f3f4f6);color:var(--text2);border:none;cursor:pointer;padding:8px 12px;font-size:11.5px" onclick="wizardCancel()">
                <i class="fa-solid fa-xmark"></i> Cancel Wizard
            </button>
        </div>`;
        addChatMsg('bot', '', html);
        // Pre-fetch clients + users in the background — they'll be needed in
        // step 2/5. Errors are silently swallowed; askClient/askAssignee will
        // simply render empty until the user retries.
        ensureWizardData().catch(() => {});
    };

    window.wizardCancel = function(){
        wizardStep = 'idle';
        updateWizardBar();
        addChatMsg('bot', '', 'Wizard cancelled. Type <b>"create task"</b> to start again or <b>"help"</b> for commands.');
    };

    window.wizardBack = function(){
        const idx = WIZARD_ORDER.indexOf(wizardStep);
        if (idx <= 0) return;
        const prev = WIZARD_ORDER[idx - 1];
        wizardStep = prev;

        switch(prev){
            case 'ask_title':
                wizardData.title = '';
                addChatMsg('bot', '', 'Going back! What is the <b>Task Title</b>?');
                break;
            case 'ask_client':
                wizardData.client_id = '';
                askClient(true);
                break;
            case 'ask_type':
                wizardData.type = '';
                askType(true);
                break;
            case 'ask_platform':
                wizardData.platform = 'instagram';
                askPlatform(true);
                break;
            case 'ask_assignee':
                wizardData.assigned_to = '';
                askAssignee(true);
                break;
            case 'ask_postdate':
                wizardData.post_date      = '';
                wizardData.deadline       = '';
                wizardData.priority       = 'normal';
                wizardData._dateInfo      = '';
                wizardData._dateInfoColor = '';
                askPostDate(true);
                break;
            case 'ask_priority':
                askPriority(true);
                break;
        }
        updateWizardBar();
    };

    function updateWizardBar(){
        const bar    = document.getElementById('acbWizardBar');
        const numEl  = document.getElementById('acbWizStepNum');
        const nameEl = document.getElementById('acbWizStepName');
        const fill   = document.getElementById('acbWizFill');
        const backBtn= document.getElementById('acbWizBackBtn');
        if (!bar) return;

        if (wizardStep === 'idle') { bar.style.display = 'none'; return; }

        bar.style.display = 'flex';
        const idx    = WIZARD_ORDER.indexOf(wizardStep);
        const stepN  = wizardStep === 'confirm' ? 'Review' : `Step ${idx + 1}`;
        const pct    = wizardStep === 'confirm' ? 100 : Math.round(((idx) / WIZARD_TOTAL) * 100);

        if (numEl)  numEl.textContent  = stepN;
        if (nameEl) nameEl.textContent = WIZARD_LABELS[wizardStep] || wizardStep;
        if (fill)   fill.style.width   = `${pct}%`;
        if (backBtn) backBtn.style.display = idx > 0 ? 'flex' : 'none';
    }

    function handleWizardStep(msg){
        if (wizardStep === 'ask_title'){
            wizardData.title = msg;
            wizardStep = 'ask_client';
            updateWizardBar();
            askClient();
        } else if (wizardStep === 'ask_postdate'){
            // Prefer the date picker value; fall back to typed text
            const picker  = document.getElementById('acbPostDatePicker');
            const dateVal = (picker && picker.value) ? picker.value : normalizeDate(msg);
            if (!dateVal) {
                addChatMsg('bot', 'Please enter a valid date (e.g., 2026-05-20) or use the date picker above.');
                return;
            }
            const calc = calcDeadlineAndPriority(dateVal);
            wizardData.post_date       = dateVal;
            wizardData.deadline        = calc.deadline;
            wizardData.priority        = calc.priority;
            wizardData._dateInfo       = calc.info;
            wizardData._dateInfoColor  = calc.infoColor;
            wizardStep = 'ask_priority';
            updateWizardBar();
            askPriority();
        }
    }

    function fmtDate(date){
        return `${date.getFullYear()}-${String(date.getMonth()+1).padStart(2,'0')}-${String(date.getDate()).padStart(2,'0')}`;
    }

    function normalizeDate(str){
        const d = new Date(str);
        if (isNaN(d.getTime())) return '';
        return fmtDate(d);
    }

    function calcDeadlineAndPriority(dateStr){
        const [y, m, d] = dateStr.split('-').map(Number);
        const postDate  = new Date(y, m - 1, d);
        const today     = new Date(); today.setHours(0,0,0,0);
        const tomorrow  = new Date(today); tomorrow.setDate(tomorrow.getDate() + 1);
        const in3Days   = new Date(today); in3Days.setDate(in3Days.getDate() + 3);

        let deadline, priority, info, infoColor;
        const daysAway = Math.round((postDate - today) / 86400000);

        if (postDate.getTime() === today.getTime()){
            deadline    = new Date(today);
            priority    = 'urgent';
            info        = 'Post is TODAY — Deadline set to TODAY — AUTO URGENT';
            infoColor   = '#ef4444';
        } else if (postDate.getTime() === tomorrow.getTime()){
            deadline    = new Date(today);
            priority    = 'urgent';
            info        = 'Post is TOMORROW — Deadline set to TODAY — AUTO URGENT';
            infoColor   = '#ef4444';
        } else if (postDate <= in3Days){
            deadline    = new Date(postDate); deadline.setDate(deadline.getDate() - 1);
            priority    = 'high';
            info        = `Post in ${daysAway} day(s) — Deadline ${daysAway - 1} day(s) away — HIGH PRIORITY`;
            infoColor   = '#f59e0b';
        } else {
            deadline    = new Date(postDate); deadline.setDate(deadline.getDate() - 5);
            priority    = 'normal';
            info        = `Post in ${daysAway} day(s) — Deadline ${daysAway - 5} day(s) away — NORMAL`;
            infoColor   = '#10b981';
        }

        if (deadline < today) deadline = new Date(today);

        return { deadline: fmtDate(deadline), priority, info, infoColor };
    }

    function askClient(isBack){
        if (!wizardDataLoaded) {
            addChatMsg('bot','','<div class="acb-typing-dots"><span></span><span></span><span></span></div>');
            ensureWizardData().then(() => askClient(isBack)).catch(() => addChatMsg('bot','Couldn\'t load client list. Try again or refresh.'));
            return;
        }
        let html = `<div>${isBack ? 'Going back! ' : 'Great. '}Which <b>Client</b> is this for?</div>`;
        html += `<div class="acb-options-grid">`;
        clientsData.slice(0, 10).forEach(c => {
            html += `<div class="acb-option-box" onclick="acbWizardSelect('client','${c.id}','${esc(c.name)}')">
                <div class="acb-opt-icon">${c.emoji || '<i class="fa-solid fa-building"></i>'}</div>
                <div class="acb-opt-info"><div class="acb-opt-name">${esc(c.name)}</div><div class="acb-opt-sub">Client</div></div>
            </div>`;
        });
        html += `</div>`;
        html += `<div style="display:flex;gap:8px;margin-top:8px">
            <button class="acb-view-all" style="flex:1;margin:0;background:var(--bg,#f3f4f6);color:var(--text2);border:none;cursor:pointer;padding:8px 12px;font-size:11.5px" onclick="wizardBack()">
                <i class="fa-solid fa-arrow-left"></i> Back to Title
            </button>
            <button class="acb-view-all" style="flex:1;margin:0;background:var(--bg,#f3f4f6);color:var(--text2);border:none;cursor:pointer;padding:8px 12px;font-size:11.5px" onclick="wizardCancel()">
                <i class="fa-solid fa-xmark"></i> Cancel
            </button>
        </div>`;
        addChatMsg('bot','',html);
    }

    function askType(isBack){
        const types = [
            {id:'reel',    label:'Reel',     icon:'fa-video'},
            {id:'post',    label:'Post',     icon:'fa-image'},
            {id:'story',   label:'Story',    icon:'fa-circle-notch'},
            {id:'carousel',label:'Carousel', icon:'fa-images'},
            {id:'website', label:'Website',  icon:'fa-globe'},
            {id:'software',label:'Software', icon:'fa-code'},
            {id:'others',  label:'Others',   icon:'fa-ellipsis'},
        ];
        let html = `<div>${isBack ? 'Going back! ' : ''}What <b>Type</b> of task is this?</div><div class="acb-options-grid">`;
        types.forEach(t => {
            html += `<div class="acb-option-box" onclick="acbWizardSelect('type','${t.id}','${t.label}')">
                <div class="acb-opt-icon"><i class="fa-solid ${t.icon}"></i></div>
                <div class="acb-opt-info"><div class="acb-opt-name">${t.label}</div><div class="acb-opt-sub">Category</div></div>
            </div>`;
        });
        html += `</div>`;
        html += `<div style="display:flex;gap:8px;margin-top:8px">
            <button class="acb-view-all" style="flex:1;margin:0;background:var(--bg,#f3f4f6);color:var(--text2);border:none;cursor:pointer;padding:8px 12px;font-size:11.5px" onclick="wizardBack()">
                <i class="fa-solid fa-arrow-left"></i> Back to Client
            </button>
            <button class="acb-view-all" style="flex:1;margin:0;background:var(--bg,#f3f4f6);color:var(--text2);border:none;cursor:pointer;padding:8px 12px;font-size:11.5px" onclick="wizardCancel()">
                <i class="fa-solid fa-xmark"></i> Cancel
            </button>
        </div>`;
        addChatMsg('bot','',html);
    }

    function askPlatform(isBack){
        const platforms = [
            {id:'instagram', label:'Instagram', icon:'fa-brands fa-instagram', color:'#E1306C'},
            {id:'facebook',  label:'Facebook',  icon:'fa-brands fa-facebook',  color:'#1877F2'},
            {id:'tiktok',    label:'TikTok',    icon:'fa-brands fa-tiktok',    color:'#010101'},
            {id:'twitter',   label:'Twitter/X', icon:'fa-brands fa-x-twitter', color:'#1DA1F2'},
            {id:'youtube',   label:'YouTube',   icon:'fa-brands fa-youtube',   color:'#FF0000'},
            {id:'linkedin',  label:'LinkedIn',  icon:'fa-brands fa-linkedin',  color:'#0A66C2'},
            {id:'website',   label:'Website',   icon:'fa-solid fa-globe',      color:'#10b981'},
            {id:'other',     label:'Other',     icon:'fa-solid fa-ellipsis',   color:'#6b7280'},
        ];
        let html = `<div>${isBack ? 'Going back! ' : ''}Which <b>Platform</b> is this for?</div><div class="acb-options-grid">`;
        platforms.forEach(p => {
            html += `<div class="acb-option-box" onclick="acbWizardSelect('platform','${p.id}','${p.label}')">
                <div class="acb-opt-icon" style="color:${p.color}"><i class="${p.icon}"></i></div>
                <div class="acb-opt-info"><div class="acb-opt-name">${p.label}</div><div class="acb-opt-sub">Platform</div></div>
            </div>`;
        });
        html += `</div>`;
        html += `<div style="display:flex;gap:8px;margin-top:8px">
            <button class="acb-view-all" style="flex:1;margin:0;background:var(--bg,#f3f4f6);color:var(--text2);border:none;cursor:pointer;padding:8px 12px;font-size:11.5px" onclick="wizardBack()">
                <i class="fa-solid fa-arrow-left"></i> Back to Type
            </button>
            <button class="acb-view-all" style="flex:1;margin:0;background:var(--bg,#f3f4f6);color:var(--text2);border:none;cursor:pointer;padding:8px 12px;font-size:11.5px" onclick="wizardCancel()">
                <i class="fa-solid fa-xmark"></i> Cancel
            </button>
        </div>`;
        addChatMsg('bot','',html);
    }

    function askAssignee(isBack){
        if (!wizardDataLoaded) {
            addChatMsg('bot','','<div class="acb-typing-dots"><span></span><span></span><span></span></div>');
            ensureWizardData().then(() => askAssignee(isBack)).catch(() => addChatMsg('bot','Couldn\'t load team list. Try again or refresh.'));
            return;
        }
        let html = `<div>${isBack ? 'Going back! ' : ''}Who should I <b>Assign</b> this to?</div><div class="acb-options-grid">`;
        usersData.forEach(u => {
            html += `<div class="acb-option-box" onclick="acbWizardSelect('assignee','${u.id}','${esc(u.name)}')">
                <div class="acb-opt-icon"><i class="fa-solid fa-user"></i></div>
                <div class="acb-opt-info"><div class="acb-opt-name">${esc(u.name)}</div><div class="acb-opt-sub">${esc(u.role)}</div></div>
            </div>`;
        });
        html += `</div>`;
        html += `<div style="display:flex;gap:8px;margin-top:8px">
            <button class="acb-view-all" style="flex:1;margin:0;background:var(--bg,#f3f4f6);color:var(--text2);border:none;cursor:pointer;padding:8px 12px;font-size:11.5px" onclick="wizardBack()">
                <i class="fa-solid fa-arrow-left"></i> Back to Platform
            </button>
            <button class="acb-view-all" style="flex:1;margin:0;background:var(--bg,#f3f4f6);color:var(--text2);border:none;cursor:pointer;padding:8px 12px;font-size:11.5px" onclick="wizardCancel()">
                <i class="fa-solid fa-xmark"></i> Cancel
            </button>
        </div>`;
        addChatMsg('bot','',html);
    }

    function askPriority(isBack){
        const priorities = [
            {id:'normal', label:'Normal', color:'#9ca3af'},
            {id:'high',   label:'High',   color:'#f59e0b'},
            {id:'urgent', label:'Urgent', color:'#ef4444'},
        ];
        const autoPriority = wizardData.priority || 'normal';
        const infoColor    = wizardData._dateInfoColor || '#9ca3af';
        const infoText     = wizardData._dateInfo || '';

        let html = `<div style="margin-bottom:6px">${isBack ? 'Going back! ' : ''}Confirm or change the <b>Priority</b>.</div>`;
        if (infoText){
            html += `<div style="display:flex;align-items:center;gap:6px;padding:6px 10px;border-radius:8px;
                background:${infoColor}1a;border:1px solid ${infoColor}33;margin-bottom:8px;font-size:11px;font-weight:600;color:${infoColor}">
                <i class="fa-solid fa-circle-info"></i> ${esc(infoText)}
            </div>`;
        }
        html += `<div class="acb-options-grid">`;
        priorities.forEach(p => {
            const isAuto = p.id === autoPriority;
            html += `<div class="acb-option-box" onclick="acbWizardSelect('priority','${p.id}','${p.label}')"
                style="${isAuto ? `border-color:${p.color};background:${p.color}1a;` : ''}">
                <div class="acb-opt-icon" style="color:${p.color};background:${p.color}1a"><i class="fa-solid fa-flag"></i></div>
                <div class="acb-opt-info">
                    <div class="acb-opt-name" style="${isAuto ? `color:${p.color}` : ''}">${p.label}${isAuto ? ' ✓' : ''}</div>
                    <div class="acb-opt-sub">${isAuto ? 'Auto-suggested' : 'Priority'}</div>
                </div>
            </div>`;
        });
        html += `</div>`;
        html += `<div style="display:flex;gap:8px;margin-top:8px">
            <button class="acb-view-all" style="flex:1;margin:0;background:var(--bg,#f3f4f6);color:var(--text2);border:none;cursor:pointer;padding:8px 12px;font-size:11.5px" onclick="wizardBack()">
                <i class="fa-solid fa-arrow-left"></i> Back to Post Date
            </button>
            <button class="acb-view-all" style="flex:1;margin:0;background:var(--bg,#f3f4f6);color:var(--text2);border:none;cursor:pointer;padding:8px 12px;font-size:11.5px" onclick="wizardCancel()">
                <i class="fa-solid fa-xmark"></i> Cancel
            </button>
        </div>`;
        addChatMsg('bot','',html);
    }

    function askPostDate(isBack){
        wizardStep = 'ask_postdate';
        updateWizardBar();
        const todayStr = new Date().toISOString().split('T')[0];
        const current  = wizardData.post_date || '';
        const html = `
            <div style="margin-bottom:8px">${isBack ? 'Going back! ' : ''}What is the <b>Post Date</b>?</div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
                <input type="date" id="acbPostDatePicker" min="${todayStr}" value="${current}"
                    style="flex:1;min-width:140px;padding:8px 10px;border:1px solid var(--border,#e5e7eb);
                    border-radius:8px;font-size:12.5px;font-family:inherit;background:var(--card,#fff);
                    color:var(--text,#111);outline:none">
                <button onclick="acbConfirmPostDate()"
                    style="padding:8px 16px;background:var(--primary);color:#fff;border:none;
                    border-radius:8px;font-weight:700;font-family:inherit;font-size:12px;cursor:pointer;
                    white-space:nowrap">
                    <i class="fa-solid fa-check" style="margin-right:4px"></i>Confirm
                </button>
            </div>
            <div style="margin-top:5px;font-size:10px;color:var(--text3,#9ca3af)">
                Design deadline will be auto-set 5 days before this date.
            </div>
            <div style="display:flex;gap:8px;margin-top:8px">
                <button class="acb-view-all" style="flex:1;margin:0;background:var(--bg,#f3f4f6);color:var(--text2);border:none;cursor:pointer;padding:8px 12px;font-size:11.5px" onclick="wizardBack()">
                    <i class="fa-solid fa-arrow-left"></i> Back to Assignee
                </button>
                <button class="acb-view-all" style="flex:1;margin:0;background:var(--bg,#f3f4f6);color:var(--text2);border:none;cursor:pointer;padding:8px 12px;font-size:11.5px" onclick="wizardCancel()">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </button>
            </div>`;
        addChatMsg('bot', '', html);
        setTimeout(() => document.getElementById('acbPostDatePicker')?.focus(), 50);
    }

    window.acbConfirmPostDate = function(){
        const picker = document.getElementById('acbPostDatePicker');
        if (!picker || !picker.value){
            if (picker) { picker.style.borderColor = '#ef4444'; setTimeout(() => { picker.style.borderColor = ''; }, 1500); }
            return;
        }
        const calc = calcDeadlineAndPriority(picker.value);
        wizardData.post_date = picker.value;
        wizardData.deadline  = calc.deadline;
        wizardData.priority  = calc.priority;
        wizardData._dateInfo = calc.info;
        wizardData._dateInfoColor = calc.infoColor;
        addChatMsg('user', 'Post Date: ' + picker.value);
        wizardStep = 'ask_priority';
        updateWizardBar();
        askPriority();
    };

    window.acbWizardSelect = function(type, id, name){
        switch(type){
            case 'client':
                wizardData.client_id    = id;
                wizardData._clientLabel = name;
                addChatMsg('user', 'Client: ' + name);
                wizardStep = 'ask_type'; updateWizardBar(); askType(); break;
            case 'type':
                wizardData.type       = id;
                wizardData._typeLabel = name;
                addChatMsg('user', 'Type: ' + name);
                wizardStep = 'ask_platform'; updateWizardBar(); askPlatform(); break;
            case 'platform':
                wizardData.platform       = id;
                wizardData._platformLabel = name;
                addChatMsg('user', 'Platform: ' + name);
                wizardStep = 'ask_assignee'; updateWizardBar(); askAssignee(); break;
            case 'assignee':
                wizardData.assigned_to    = id;
                wizardData._assigneeLabel = name;
                addChatMsg('user', 'Assign to: ' + name);
                wizardStep = 'ask_postdate'; updateWizardBar(); askPostDate(); break;
            case 'priority':
                wizardData.priority = id;
                addChatMsg('user', 'Priority: ' + name);
                wizardStep = 'confirm'; updateWizardBar(); showConfirmation(); break;
        }
    };

    function showConfirmation(){
        const infoColor = wizardData._dateInfoColor || 'var(--primary)';
        const infoText  = wizardData._dateInfo || '';
        const priorityColors = { urgent:'#ef4444', high:'#f59e0b', normal:'#9ca3af' };
        const priColor = priorityColors[wizardData.priority] || '#9ca3af';

        let html = `<div style="font-weight:700;margin-bottom:8px">Confirm Task Creation:</div>`;

        if (infoText){
            html += `<div style="display:flex;align-items:center;gap:6px;padding:7px 10px;border-radius:8px;
                background:${infoColor}1a;border:1px solid ${infoColor}33;margin-bottom:8px;
                font-size:10.5px;font-weight:600;color:${infoColor}">
                <i class="fa-solid fa-calendar-check"></i> ${esc(infoText)}
            </div>`;
        }

        const clientLabel   = wizardData._clientLabel   || clientsData.find(c=>c.id==wizardData.client_id)?.name || '—';
        const assigneeLabel = wizardData._assigneeLabel || usersData.find(u=>u.id==wizardData.assigned_to)?.name || '—';

        html += `
        <div style="font-size:11.5px;background:rgba(var(--primary-rgb),.05);padding:10px 12px;border-radius:10px;border:1px solid rgba(var(--primary-rgb),.1);display:flex;flex-direction:column;gap:5px">
            <div><b>Title:</b> ${esc(wizardData.title)}</div>
            <div><b>Client:</b> ${esc(clientLabel)}</div>
            <div><b>Type:</b> ${esc(wizardData._typeLabel || wizardData.type)} &nbsp;·&nbsp; <b>Platform:</b> ${esc(wizardData._platformLabel || wizardData.platform)}</div>
            <div><b>Assignee:</b> ${esc(assigneeLabel)}</div>
            <div><b>Priority:</b> <span style="color:${priColor};font-weight:800">${esc(wizardData.priority.toUpperCase())}</span> <span style="opacity:.5;font-size:10px">(auto from date)</span></div>
            <div><b>Post Date:</b> ${esc(wizardData.post_date)}</div>
            <div><b>Deadline:</b> <span style="font-weight:700">${esc(wizardData.deadline)}</span> <span style="opacity:.5;font-size:10px">(auto)</span></div>
        </div>
        <div style="display:flex;gap:8px;margin-top:10px">
            <button class="acb-view-all" style="flex:1;margin:0;background:var(--primary);color:#fff;border:none;cursor:pointer" onclick="acbFinalizeTask()">
                <i class="fa-solid fa-check"></i> Create Task
            </button>
            <button class="acb-view-all" style="flex:0 0 auto;margin:0;background:var(--bg,#f3f4f6);color:var(--text2);border:none;cursor:pointer;padding:10px 14px" onclick="wizardBack()">
                <i class="fa-solid fa-arrow-left"></i> Back
            </button>
            <button class="acb-view-all" style="flex:0 0 auto;margin:0;background:var(--bg,#f3f4f6);color:var(--text2);border:none;cursor:pointer;padding:10px 14px" onclick="startWizard(true)" title="Restart">
                <i class="fa-solid fa-rotate"></i>
            </button>
        </div>`;
        addChatMsg('bot','',html);
    }

    window.acbFinalizeTask = function(){
        document.getElementById('acbLoading').classList.add('show');
        const formData = new FormData();
        formData.append('title',       wizardData.title);
        formData.append('client_id',   wizardData.client_id);
        formData.append('type',        wizardData.type);
        formData.append('platform',    wizardData.platform);
        formData.append('assigned_to', wizardData.assigned_to);
        formData.append('priority',    wizardData.priority);
        formData.append('deadline',    wizardData.deadline);
        formData.append('post_date',   wizardData.post_date);
        formData.append('status',      'todo');
        formData.append('_token',      document.querySelector('meta[name="csrf-token"]')?.content || '');

        fetch('/admin/tasks', { method:'POST', body:formData, headers:{'X-Requested-With':'XMLHttpRequest'} })
            .then(r => {
                document.getElementById('acbLoading').classList.remove('show');
                if (r.ok){
                    wizardStep = 'idle'; updateWizardBar();
                    addChatMsg('bot','','<span style="color:#10b981;font-weight:700">✅ Task created successfully!</span><br>The task has been created and assigned.');
                    setTimeout(() => { acbRefresh(); }, 1000);
                } else {
                    return r.json().then(err => {
                        let html = `<div style="color:#ef4444;font-weight:700;margin-bottom:6px">❌ Could not create task.</div>`;
                        if (err.errors) {
                            html += `<ul style="margin:0 0 8px;padding-left:16px;font-size:11.5px;color:#ef4444">`;
                            Object.values(err.errors).forEach(msgs => msgs.forEach(m => { html += `<li>${esc(m)}</li>`; }));
                            html += `</ul>`;
                        } else if (err.message) {
                            html += `<div style="font-size:11.5px;color:#ef4444;margin-bottom:8px">${esc(err.message)}</div>`;
                        }
                        html += `<div style="display:flex;gap:6px">
                            <button class="acb-view-all" style="margin:0;padding:8px;flex:1" onclick="wizardBack()"><i class="fa-solid fa-arrow-left"></i> Fix &amp; Retry</button>
                        </div>`;
                        addChatMsg('bot','',html);
                    });
                }
            })
            .catch(() => {
                document.getElementById('acbLoading').classList.remove('show');
                addChatMsg('bot','','<div style="color:#ef4444">❌ Network error. Please try again.</div>');
            });
    };

    /* ── Inline Status Update ── */
    window.acbUpdateTaskStatus = function(taskId, newStatus, btn){
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        btn.classList.add('acb-status-updating');

        fetch(`/admin/tasks/${taskId}/kanban-status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN':     csrfToken,
                'Content-Type':     'application/json',
                'Accept':           'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success){
                btn.classList.remove('acb-status-updating');
                btn.closest('.acb-status-btns')?.querySelectorAll('.acb-status-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active','acb-status-success');
                // Update the status badge in the same row
                const item = btn.closest('.acb-list-item');
                if (item){
                    const badge = item.querySelector('.acb-status');
                    if (badge){ badge.className = `acb-status ${newStatus}`; badge.textContent = acbFormatStatus(newStatus); }
                    const dot = item.querySelector('.acb-list-dot');
                    if (dot){ dot.className = `acb-list-dot ${newStatus}`; }
                }
                setTimeout(() => {
                    btn.classList.remove('acb-status-success');
                    loadTabData('overview');
                }, 900);
            }
        })
        .catch(() => {
            btn.classList.remove('acb-status-updating');
            const orig = btn.textContent;
            btn.textContent = 'Error';
            setTimeout(() => { btn.textContent = orig; }, 1500);
        });
    };

    /* ── Renderers ── */
    function renderOverview(d){
        const setText = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v ?? 0; };
        setText('valTotalTasks',   d.totalTasks);
        setText('valInProgress',   d.inProgress);
        setText('valPendingReview',d.pendingReview);
        setText('valCompleted',    d.completedWeek);
        setText('valOverdue',      d.delayedTasks);
        setText('valClients',      d.activeClients);
        setText('valTodo',         d.todoTasks);
        setText('valUnassigned',   d.unassignedTasks);
        setText('valPublished',    d.publishedTasks);

        document.getElementById('cardOverdue')?.classList.toggle('acb-stat-alert', (d.delayedTasks ?? 0) > 0);
        document.getElementById('cardUnassigned')?.classList.toggle('acb-stat-warn', (d.unassignedTasks ?? 0) > 0);
    }

    function renderTasks(d){
        const el          = document.getElementById('acbTasksList');
        const countEl     = document.getElementById('acbTaskCount');
        const filterCount = document.getElementById('acbFilterCount');
        const total       = d.total || 0;
        if (countEl)     countEl.textContent     = `${total} tasks`;
        if (filterCount) filterCount.textContent = total;

        if (!d.tasks?.length){
            const msg = activeFilter ? `No ${activeFilterLabel.toLowerCase()} found` : 'No tasks found';
            el.innerHTML = `<div class="acb-empty"><i class="fa-solid fa-inbox"></i><span>${esc(msg)}</span></div>`;
            return;
        }

        el.innerHTML = d.tasks.map(t => {
            const statusCls   = t.overdue ? 'overdue' : (t.status || 'todo');
            const statusLabel = t.overdue ? 'Overdue' : acbFormatStatus(t.status);
            const allStatuses = ['todo','inprogress','review','completed','published'];
            const statusBtns  = allStatuses.map(s => `
                <button class="acb-status-btn ${s === t.status && !t.overdue ? 'active' : ''}"
                    onclick="event.stopPropagation();acbUpdateTaskStatus(${t.id},'${s}',this)">${acbFormatStatus(s)}</button>`).join('');

            return `
            <div class="acb-list-item" onclick="acbExpandItem(this)" data-task-id="${t.id}">
                <div class="acb-list-dot ${statusCls}"></div>
                <div class="acb-list-info">
                    <div class="acb-list-title">${esc(t.title)}</div>
                    <div class="acb-list-meta">
                        <span class="acb-list-sub"><i class="fa-solid fa-building" style="font-size:9px"></i> ${esc(t.client)}</span>
                        <span class="acb-list-sub"><i class="fa-solid fa-user" style="font-size:9px"></i> ${esc(t.assignee)}</span>
                    </div>
                    <div class="acb-list-meta" style="margin-top:2px">
                        <span class="acb-status ${statusCls}">${statusLabel}</span>
                        ${t.priority && t.priority !== 'normal' ? `<span class="acb-priority ${t.priority}"><i class="fa-solid fa-flag"></i> ${esc(t.priority)}</span>` : ''}
                    </div>
                </div>
                <div class="acb-list-right">
                    <div class="acb-list-badge">${esc(t.date)}</div>
                    <i class="fa-solid fa-chevron-right acb-list-expand-icon"></i>
                </div>
                <div class="acb-detail" style="display:none" onclick="event.stopPropagation()">
                    <div class="acb-detail-row"><span class="acb-detail-label">Client</span><span class="acb-detail-value">${esc(t.client)}</span></div>
                    <div class="acb-detail-row"><span class="acb-detail-label">Assignee</span><span class="acb-detail-value">${esc(t.assignee)}</span></div>
                    <div class="acb-detail-row"><span class="acb-detail-label">Deadline</span><span class="acb-detail-value">${esc(t.date)}</span></div>
                    ${t.overdue ? '<div class="acb-detail-alert"><i class="fa-solid fa-triangle-exclamation"></i> This task is overdue</div>' : ''}
                    <div class="acb-status-change">
                        <span class="acb-detail-label" style="margin-top:4px">Status:</span>
                        <div class="acb-status-btns">${statusBtns}</div>
                    </div>
                    <div class="acb-detail-actions">
                        <a href="/admin/tasks/${t.id}" class="acb-detail-link" onclick="event.stopPropagation()">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Task
                        </a>
                    </div>
                </div>
            </div>`;
        }).join('');
    }

    function renderClients(d){
        const el      = document.getElementById('acbClientsList');
        const countEl = document.getElementById('acbClientCount');
        if (countEl) countEl.textContent = `${d.total || 0} clients`;

        if (!d.clients?.length){
            el.innerHTML = '<div class="acb-empty"><i class="fa-solid fa-building"></i><span>No clients found</span></div>';
            return;
        }

        el.innerHTML = d.clients.map(c => `
            <div class="acb-list-item" onclick="acbExpandItem(this)">
                <div class="acb-list-avatar">${esc((c.name||'?').charAt(0).toUpperCase())}</div>
                <div class="acb-list-info">
                    <div class="acb-list-title">${esc(c.name)}</div>
                    <div class="acb-list-meta">
                        ${c.phone ? `<span class="acb-list-sub"><i class="fa-solid fa-phone" style="font-size:9px"></i> ${esc(c.phone)}</span>` : ''}
                        ${c.email ? `<span class="acb-list-sub"><i class="fa-solid fa-envelope" style="font-size:9px"></i> ${esc(c.email)}</span>` : ''}
                    </div>
                </div>
                <div class="acb-list-right">
                    <div class="acb-list-badge">${c.tasks} tasks</div>
                    <i class="fa-solid fa-chevron-right acb-list-expand-icon"></i>
                </div>
                <div class="acb-detail" style="display:none" onclick="event.stopPropagation()">
                    <div class="acb-detail-row"><span class="acb-detail-label">Phone</span><span class="acb-detail-value">${esc(c.phone||'N/A')}</span></div>
                    <div class="acb-detail-row"><span class="acb-detail-label">Email</span><span class="acb-detail-value">${esc(c.email||'N/A')}</span></div>
                    <div class="acb-detail-row"><span class="acb-detail-label">Tasks</span><span class="acb-detail-value">${c.tasks} assigned</span></div>
                    <div class="acb-detail-actions">
                        <a href="/admin/clients/${c.id}" class="acb-detail-link" onclick="event.stopPropagation()">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Client
                        </a>
                    </div>
                </div>
            </div>`).join('');
    }

    window.acbExpandItem = function(el){
        const detail = el.querySelector('.acb-detail');
        if (!detail) return;
        const isOpen = detail.style.display !== 'none';
        document.querySelectorAll('.acb-detail').forEach(d => { d.style.display='none'; d.parentElement.classList.remove('acb-expanded'); });
        if (!isOpen){ detail.style.display='flex'; el.classList.add('acb-expanded'); setTimeout(() => el.scrollIntoView({behavior:'smooth',block:'nearest'}), 50); }
    };

    function renderReports(d){
        const bars = [
            {label:'Todo',       val:d.todoTasks||0,      color:'#6b7280', filter:'todo'},
            {label:'In Progress',val:d.inProgress||0,     color:'#3b82f6', filter:'inprogress'},
            {label:'Review',     val:d.reviewTasks||0,    color:'#f59e0b', filter:'review'},
            {label:'Completed',  val:d.completedTotal||0, color:'#10b981', filter:'completed'},
            {label:'Published',  val:d.publishedTotal||0, color:'#059669', filter:'published'},
            {label:'Overdue',    val:d.delayedTasks||0,   color:'#ef4444', filter:'overdue'},
            {label:'Unassigned', val:d.unassignedTasks||0,color:'#8b5cf6', filter:'unassigned'},
        ];
        const mx = Math.max(1, ...bars.map(b => b.val));
        document.getElementById('acbReportBars').innerHTML = bars.map(b => `
            <div class="acb-bar-row" onclick="acbDrillDown('tasks','${b.filter}','${b.label} Tasks')" title="View ${b.label.toLowerCase()} tasks">
                <div class="acb-bar-label">${b.label}</div>
                <div class="acb-bar-track"><div class="acb-bar-fill" style="width:${(b.val/mx)*100}%;background:${b.color}"></div></div>
                <div class="acb-bar-val">${b.val}</div>
            </div>`).join('');

        document.getElementById('acbSummaryGrid').innerHTML = [
            {label:'Total',     val:d.totalTasks||0,     color:'var(--primary)'},
            {label:'This Week', val:d.completedWeek||0,  color:'#10b981'},
            {label:'Overdue',   val:d.delayedTasks||0,   color:'#ef4444'},
            {label:'Unassigned',val:d.unassignedTasks||0,color:'#8b5cf6'},
        ].map(s => `
            <div class="acb-summary-item">
                <div class="acb-summary-dot" style="background:${s.color}"></div>
                <div class="acb-summary-text">${s.label}</div>
                <div class="acb-summary-num">${s.val}</div>
            </div>`).join('');
    }

    /* ── Helpers ── */
    window.acbFormatStatus = function(s){
        return {todo:'Todo',inprogress:'In Progress',review:'Review',completed:'Completed',published:'Published'}[s] || (s||'Unknown');
    };

    function esc(s){ const d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }
})();
