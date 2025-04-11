function initializeSchedules() {
    // Load schedules from localStorage or use default schedules
    const savedSchedules = localStorage.getItem('schedules');
    if (savedSchedules) {
        schedules = JSON.parse(savedSchedules);
    }
    updateScheduleDisplay();
}

function updateScheduleDisplay() {
    const scheduleList = document.getElementById('scheduleList');
    scheduleList.innerHTML = '';

    schedules.forEach(schedule => {
        const scheduleItem = createScheduleItem(schedule);
        scheduleList.appendChild(scheduleItem);
    });
    
    // Add button to add new schedule
    const addButton = document.createElement('button');
    addButton.className = 'add-schedule-btn';
    addButton.innerHTML = '<i class="fas fa-plus"></i> Add Schedule';
    scheduleList.appendChild(addButton);
}

function createScheduleItem(schedule) {
    const item = document.createElement('div');
    item.className = 'schedule-item';
    
    // Format actions into badges
    const actionBadges = schedule.actions.map(action => 
        `<span class="schedule-action">${action}</span>`
    ).join('');
    
    item.innerHTML = `
        <div class="schedule-time">${schedule.time}</div>
        <div class="schedule-details">
            <div class="schedule-name">${schedule.name}</div>
            <div class="schedule-actions">
                ${actionBadges}
            </div>
        </div>
        <div class="schedule-controls">
            <button class="edit-btn" title="Edit"><i class="fas fa-edit"></i></button>
            <button class="delete-btn" title="Delete"><i class="fas fa-trash"></i></button>
        </div>
    `;
    
    // Add hover effect for schedule item
    item.addEventListener('mouseenter', () => {
        item.classList.add('schedule-hover');
    });
    
    item.addEventListener('mouseleave', () => {
        item.classList.remove('schedule-hover');
    });
    
    return item;
}

// Add styles for schedule components
const scheduleStyles = document.createElement('style');
scheduleStyles.innerHTML = `
    .schedule-controls {
        display: flex;
        gap: 10px;
    }
    
    .edit-btn, .delete-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: none;
        background-color: #f0f0f0;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
    }
    
    .edit-btn:hover {
        background-color: var(--primary-color);
        color: white;
    }
    
    .delete-btn:hover {
        background-color: var(--danger-color);
        color: white;
    }
    
    .schedule-hover {
        background-color: rgba(58, 134, 255, 0.05);
    }
    
    .add-schedule-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 12px 20px;
        background-color: var(--primary-color);
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 500;
        transition: var(--transition);
        margin-top: 10px;
    }
    
    .add-schedule-btn:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
    }
`;
document.head.appendChild(scheduleStyles); 