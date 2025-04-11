// New completely recreated main.js file
// Initialize the application
document.addEventListener('DOMContentLoaded', () => {
    console.log("DOM Loaded in main.js");
    initializeDevices();
    initializeSchedules();
    setupEventListeners();
});

// The devices array is defined in devices.js
// No need to redefine it here
console.log("In main.js, checking devices:", typeof devices, devices ? devices.length : "not defined");

// Sample schedule data
const schedules = [
    { id: 1, name: 'Morning Routine', time: '07:00', actions: ['Turn on lights', 'Set thermostat to 72°'] },
    { id: 2, name: 'Night Mode', time: '22:00', actions: ['Turn off lights', 'Lock doors', 'Set thermostat to 68°'] }
];

function setupEventListeners() {
    console.log("Setting up event listeners");
    // Quick action buttons
    document.getElementById('allOff').addEventListener('click', () => {
        console.log("All Off clicked");
        devices.forEach(device => device.status = 'off');
        updateDeviceDisplay();
        saveDevices();
    });

    document.getElementById('movieMode').addEventListener('click', activateMovieMode);
    document.getElementById('sleepMode').addEventListener('click', activateSleepMode);
    document.getElementById('awayMode').addEventListener('click', activateAwayMode);
    
    // Close modal when clicking the X
    const closeBtn = document.querySelector('.modal .close');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            document.getElementById('addDeviceModal').style.display = 'none';
        });
    }
    
    // Handle form submission for adding new device
    const addDeviceForm = document.getElementById('addDeviceForm');
    if (addDeviceForm) {
        addDeviceForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const deviceName = document.getElementById('deviceName').value;
            const deviceType = document.getElementById('deviceType').value;
            
            // Add new device
            const newDevice = {
                id: Date.now(), // Use timestamp as unique ID
                name: deviceName,
                type: deviceType,
                status: 'off'
            };
            
            // Add temperature property for thermostats
            if (deviceType === 'thermostat') {
                newDevice.temperature = 72;
            }
            
            devices.push(newDevice);
            updateDeviceDisplay();
            saveDevices();
            
            // Close modal and reset form
            document.getElementById('addDeviceModal').style.display = 'none';
            addDeviceForm.reset();
        });
    }
} 
