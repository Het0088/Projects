// Define the devices array globally
let devices = [];

function initializeDevices() {
    console.log("Initialize Devices called");
    // Load devices from localStorage or use default devices
    const savedDevices = localStorage.getItem('devices');
    if (savedDevices) {
        console.log("Loading devices from localStorage:", savedDevices);
        devices = JSON.parse(savedDevices);
    } else {
        console.log("No saved devices, initializing with defaults");
        // If no saved devices, initialize with default devices
        devices = [
            { id: 1, name: 'Living Room Light', type: 'light', status: 'off' },
            { id: 2, name: 'Thermostat', type: 'thermostat', status: 'on', temperature: 72 },
            { id: 3, name: 'Front Door Lock', type: 'lock', status: 'locked' },
            { id: 4, name: 'Security Camera', type: 'camera', status: 'on' }
        ];
        // Save default devices to localStorage
        saveDevices();
    }
    
    console.log("Devices array after initialization:", devices);
    // Make sure to update the device display
    updateDeviceDisplay();
    
    // Add button to add new device
    const devicesSection = document.querySelector('.devices');
    const addButton = document.createElement('button');
    addButton.className = 'add-device-btn';
    addButton.innerHTML = '<i class="fas fa-plus"></i> Add Device';
    addButton.onclick = showAddDeviceModal;
    devicesSection.appendChild(addButton);
}

function toggleDevice(deviceId) {
    const device = devices.find(d => d.id === deviceId);
    if (device) {
        // Add animation class to card before toggling
        const card = document.querySelector(`.device-card[data-id="${deviceId}"]`);
        card.classList.add('toggling');
        
        setTimeout(() => {
            device.status = device.status === 'on' ? 'off' : 'on';
            updateDeviceDisplay();
            saveDevices();
            
            // Remove animation class
            setTimeout(() => {
                const updatedCard = document.querySelector(`.device-card[data-id="${deviceId}"]`);
                if (updatedCard) updatedCard.classList.remove('toggling');
            }, 300);
        }, 300);
    }
}

function saveDevices() {
    localStorage.setItem('devices', JSON.stringify(devices));
}

function createDeviceCard(device) {
    const card = document.createElement('div');
    card.className = `device-card ${device.status === 'on' ? 'device-on' : 'device-off'}`;
    card.setAttribute('data-id', device.id);
    
    // Choose icon based on device type
    let typeIcon = 'lightbulb';
    if (device.type === 'thermostat') typeIcon = 'temperature-high';
    else if (device.type === 'lock') typeIcon = 'lock';
    else if (device.type === 'camera') typeIcon = 'video';
    
    // Build the card HTML with consistent layout
    let cardHTML = `
        <div class="type-icon"><i class="fas fa-${typeIcon}"></i></div>
        <h3>${device.name}</h3>
        <div class="status">
            <span class="status-indicator ${device.status === 'on' ? 'status-on' : 'status-off'}"></span>
            <span>${device.status === 'on' ? 'Active' : 'Inactive'}</span>
        </div>`;
    
    // For thermostat, show temperature in a specific way
    if (device.type === 'thermostat') {
        cardHTML += `
        <div class="temperature-display">
            <span class="current-temp">${device.temperature}°F</span>
        </div>
        <div class="control-buttons-row">
            <button onclick="toggleDevice(${device.id})" class="toggle-btn ${device.status === 'on' ? 'btn-active' : ''}">
                <i class="fas fa-power-off"></i> ${device.status === 'on' ? 'TURN OFF' : 'TURN ON'}
            </button>
        </div>
        <div class="temp-controls-row">
            <button class="temp-btn minus-btn" onclick="adjustTemperature(${device.id}, -1)">
                <i class="fas fa-minus"></i>
            </button>
            <button class="temp-btn plus-btn" onclick="adjustTemperature(${device.id}, 1)">
                <i class="fas fa-plus"></i>
            </button>
        </div>`;
    } else {
        // For all other devices, just show the toggle button in consistent position
        cardHTML += `
        <div class="spacer"></div>
        <div class="control-buttons-row">
            <button onclick="toggleDevice(${device.id})" class="toggle-btn ${device.status === 'on' ? 'btn-active' : ''}">
                <i class="fas fa-power-off"></i> ${device.status === 'on' ? 'TURN OFF' : 'TURN ON'}
            </button>
        </div>`;
    }
    
    card.innerHTML = cardHTML;
    return card;
}

function adjustTemperature(deviceId, change) {
    const device = devices.find(d => d.id === deviceId);
    if (device && device.type === 'thermostat') {
        // Find the current temperature display
        const card = document.querySelector(`.device-card[data-id="${deviceId}"]`);
        if (card) {
            const tempDisplay = card.querySelector('.current-temp');
            
            // Add animation class based on temperature change
            if (change > 0) {
                tempDisplay.classList.remove('decreasing');
                tempDisplay.classList.add('increasing');
            } else {
                tempDisplay.classList.remove('increasing');
                tempDisplay.classList.add('decreasing');
            }
            
            // Show visual feedback on the button
            const btnIndex = change > 0 ? 1 : 0;
            const btn = card.querySelectorAll('.temp-btn')[btnIndex];
            if (btn) {
                btn.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    btn.style.transform = '';
                }, 200);
            }
            
            // Update temperature with animation
            device.temperature += change;
            
            // Create and show a floating number that indicates the change
            const floatingNumber = document.createElement('span');
            floatingNumber.className = 'floating-number';
            floatingNumber.textContent = change > 0 ? '+1°' : '-1°';
            floatingNumber.style.color = change > 0 ? '#27ae60' : '#e74c3c';
            floatingNumber.style.position = 'absolute';
            floatingNumber.style.fontSize = '18px';
            floatingNumber.style.fontWeight = 'bold';
            floatingNumber.style.top = '0';
            floatingNumber.style.left = '50%';
            floatingNumber.style.transform = 'translateX(-50%)';
            floatingNumber.style.opacity = '0';
            floatingNumber.style.transition = 'all 1s ease-out';
            
            tempDisplay.style.position = 'relative';
            tempDisplay.appendChild(floatingNumber);
            
            // Animate the floating number
            setTimeout(() => {
                floatingNumber.style.top = '-20px';
                floatingNumber.style.opacity = '1';
            }, 10);
            
            // Remove floating number after animation completes
            setTimeout(() => {
                floatingNumber.style.opacity = '0';
                setTimeout(() => {
                    if (floatingNumber.parentNode) {
                        floatingNumber.parentNode.removeChild(floatingNumber);
                    }
                }, 300);
            }, 1000);
            
            // Remove animation classes after animation completes
            setTimeout(() => {
                tempDisplay.classList.remove('increasing', 'decreasing');
            }, 1000);
            
            // Show a notification for significant temperature changes
            if (device.temperature >= 78) {
                showNotification('High Temperature Alert', 'Temperature is set quite high. Consider energy saving.');
            } else if (device.temperature <= 65) {
                showNotification('Low Temperature Alert', 'Temperature is set quite low. Consider comfort levels.');
            }
            
            // Update and save device data
            updateDeviceDisplay();
            saveDevices();
        }
    }
}

function activateMovieMode() {
    // Add animation to the button
    document.getElementById('movieMode').classList.add('active-mode');
    
    // Implement movie mode logic with animations
    devices.forEach(device => {
        if (device.type === 'light') {
            device.status = 'off';
        }
    });
    
    // Show notification
    showNotification('Movie Mode Activated', 'Lights dimmed for the perfect viewing experience');
    
    setTimeout(() => {
        updateDeviceDisplay();
        saveDevices();
        document.getElementById('movieMode').classList.remove('active-mode');
    }, 800);
}

function activateSleepMode() {
    // Add animation to the button
    document.getElementById('sleepMode').classList.add('active-mode');
    
    // Implement sleep mode logic
    devices.forEach(device => {
        if (device.type === 'light') {
            device.status = 'off';
        }
        if (device.type === 'thermostat') {
            device.temperature = 68;
        }
    });
    
    // Show notification
    showNotification('Sleep Mode Activated', 'All set for a good night\'s rest');
    
    setTimeout(() => {
        updateDeviceDisplay();
        saveDevices();
        document.getElementById('sleepMode').classList.remove('active-mode');
    }, 800);
}

function activateAwayMode() {
    // Add animation to the button
    document.getElementById('awayMode').classList.add('active-mode');
    
    // Implement away mode logic
    devices.forEach(device => {
        if (device.type === 'light') {
            device.status = 'off';
        }
        if (device.type === 'lock') {
            device.status = 'locked';
        }
        if (device.type === 'camera') {
            device.status = 'on';
        }
    });
    
    // Show notification
    showNotification('Away Mode Activated', 'Your home is now secured');
    
    setTimeout(() => {
        updateDeviceDisplay();
        saveDevices();
        document.getElementById('awayMode').classList.remove('active-mode');
    }, 800);
}

function showNotification(title, message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.innerHTML = `
        <div class="notification-title">${title}</div>
        <div class="notification-message">${message}</div>
    `;
    document.body.appendChild(notification);
    
    // Animate notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Remove notification after delay
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

function showAddDeviceModal() {
    const modal = document.getElementById('addDeviceModal');
    modal.style.display = 'block';
}

function updateDeviceDisplay() {
    console.log("updateDeviceDisplay called");
    // Get the devices grid element
    const devicesGrid = document.getElementById('devicesGrid');
    
    console.log("devicesGrid element:", devicesGrid);
    console.log("Current devices array:", devices);
    
    // Clear existing content
    if (devicesGrid) {
        devicesGrid.innerHTML = '';
        
        // Add each device card to the grid
        devices.forEach(device => {
            console.log("Creating card for device:", device);
            const deviceCard = createDeviceCard(device);
            devicesGrid.appendChild(deviceCard);
            console.log("Device card added to grid");
        });
        
        // Show a message if no devices are available
        if (devices.length === 0) {
            console.log("No devices available, showing message");
            const noDevicesMsg = document.createElement('div');
            noDevicesMsg.className = 'no-devices-message';
            noDevicesMsg.textContent = 'No devices available. Click "Add Device" to get started.';
            devicesGrid.appendChild(noDevicesMsg);
        }
    } else {
        console.error('Device grid element not found. Check if #devicesGrid exists in the HTML.');
    }
}

// Add styles for notifications
const style = document.createElement('style');
style.innerHTML = `
    .notification {
        position: fixed;
        top: 20px;
        right: -300px;
        background-color: var(--primary-color);
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: var(--shadow-md);
        z-index: 1000;
        transition: right 0.3s ease-out;
        width: 280px;
    }
    
    .notification.show {
        right: 20px;
    }
    
    .notification-title {
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .notification-message {
        font-size: 14px;
        opacity: 0.9;
    }
    
    .device-card.toggling {
        animation: cardToggle 0.6s ease;
    }
    
    @keyframes cardToggle {
        0% { transform: scale(1); }
        50% { transform: scale(0.95); }
        100% { transform: scale(1); }
    }
    
    .active-mode {
        animation: activateMode 0.8s ease;
    }
    
    @keyframes activateMode {
        0% { transform: scale(1); }
        50% { transform: scale(0.9); background-color: var(--primary-color); color: white; }
        100% { transform: scale(1); }
    }
    
    .add-device-btn {
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
        margin-top: 20px;
    }
    
    .add-device-btn:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
    }
    
    .temperature-control {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 10px 0;
    }
    
    .current-temp {
        font-size: 24px;
        font-weight: 600;
        color: var(--primary-color);
    }
    
    .temp-controls {
        display: flex;
        gap: 10px;
    }
    
    .temp-btn {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: none;
        background-color: #f0f0f0;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
    }
    
    .temp-btn:hover {
        background-color: var(--primary-light);
        color: white;
    }
    
    .btn-active {
        background-color: var(--success-color);
    }
    
    .btn-active:hover {
        background-color: var(--danger-color);
    }
`;
document.head.appendChild(style); 