// Initialize advanced features
document.addEventListener('DOMContentLoaded', () => {
    initializeEnergyChart();
    initializeVoiceCommands();
    initializeSceneCreator();
});

// Energy Chart with Chart.js (add Chart.js in your HTML)
function initializeEnergyChart() {
    // Create a simple energy chart animation without Chart.js
    const canvas = document.getElementById('energyChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    canvas.width = canvas.parentElement.clientWidth;
    canvas.height = 220;
    
    // Fake data
    const data = [20, 25, 30, 28, 35, 40, 38, 45, 42, 48, 50, 47];
    const maxData = Math.max(...data) * 1.2;
    
    // Chart dimensions
    const chartWidth = canvas.width - 40;
    const chartHeight = canvas.height - 40;
    const barWidth = chartWidth / data.length * 0.6;
    const barSpacing = chartWidth / data.length * 0.4;
    
    // Animation variables
    let animationFrame = 0;
    const totalFrames = 60;
    
    function drawChart() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Draw axes
        ctx.beginPath();
        ctx.moveTo(30, 10);
        ctx.lineTo(30, chartHeight + 20);
        ctx.lineTo(chartWidth + 40, chartHeight + 20);
        ctx.strokeStyle = '#ddd';
        ctx.stroke();
        
        // Calculate animation progress
        const progress = Math.min(1, animationFrame / totalFrames);
        
        // Draw bars
        for (let i = 0; i < data.length; i++) {
            const x = 40 + i * (barWidth + barSpacing);
            const normalizedHeight = (data[i] / maxData) * chartHeight * progress;
            const y = chartHeight + 20 - normalizedHeight;
            
            // Gradient for bars
            const gradient = ctx.createLinearGradient(0, y, 0, chartHeight + 20);
            gradient.addColorStop(0, '#3a86ff');
            gradient.addColorStop(1, '#8ecae6');
            
            ctx.fillStyle = gradient;
            ctx.beginPath();
            ctx.roundRect(x, y, barWidth, normalizedHeight, 5);
            ctx.fill();
            
            // Draw hour labels
            ctx.fillStyle = '#6c757d';
            ctx.font = '10px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(`${i + 8}h`, x + barWidth / 2, chartHeight + 35);
        }
        
        // Continue animation
        if (animationFrame < totalFrames) {
            animationFrame++;
            requestAnimationFrame(drawChart);
        }
    }
    
    // Start animation
    drawChart();
    
    // Redraw on window resize
    window.addEventListener('resize', () => {
        canvas.width = canvas.parentElement.clientWidth;
        chartWidth = canvas.width - 40;
        barWidth = chartWidth / data.length * 0.6;
        barSpacing = chartWidth / data.length * 0.4;
        animationFrame = 0;
        drawChart();
    });
}

// Voice Commands
function initializeVoiceCommands() {
    const voiceButton = document.getElementById('voiceButton');
    const voiceText = document.getElementById('voiceText');
    const voiceStatus = document.getElementById('voiceStatus');
    
    if (!voiceButton) return;
    
    let isListening = false;
    
    voiceButton.addEventListener('click', () => {
        if (isListening) {
            stopListening();
        } else {
            startListening();
        }
    });
    
    function startListening() {
        isListening = true;
        voiceButton.classList.add('listening');
        voiceText.textContent = "Listening...";
        voiceStatus.textContent = "Speak now";
        
        // Simulate voice recognition after 3 seconds
        setTimeout(() => {
            processVoiceCommand();
        }, 3000);
    }
    
    function stopListening() {
        isListening = false;
        voiceButton.classList.remove('listening');
        voiceText.textContent = "Tap to speak";
        voiceStatus.textContent = "Ready";
    }
    
    function processVoiceCommand() {
        // Simulate voice recognition with random commands
        const commands = [
            "Turn on living room lights",
            "Set temperature to 72 degrees",
            "Activate movie mode"
        ];
        const randomCommand = commands[Math.floor(Math.random() * commands.length)];
        
        voiceText.textContent = `"${randomCommand}"`;
        voiceStatus.textContent = "Processing...";
        
        // Simulate processing
        setTimeout(() => {
            // Execute the command
            if (randomCommand.includes("lights")) {
                // Turn on lights logic
                const lightsDevice = devices.find(d => d.name.toLowerCase().includes("light"));
                if (lightsDevice) {
                    lightsDevice.status = "on";
                    updateDeviceDisplay();
                    saveDevices();
                }
                voiceStatus.textContent = "Lights turned on!";
            } else if (randomCommand.includes("temperature")) {
                // Set temperature logic
                const thermostat = devices.find(d => d.type === "thermostat");
                if (thermostat) {
                    thermostat.temperature = 72;
                    updateDeviceDisplay();
                    saveDevices();
                }
                voiceStatus.textContent = "Temperature set to 72°F";
            } else if (randomCommand.includes("movie mode")) {
                // Activate movie mode
                activateMovieMode();
                voiceStatus.textContent = "Movie mode activated!";
            }
            
            // Reset after a few seconds
            setTimeout(stopListening, 2000);
        }, 1000);
    }
}

// Scene Creator
function initializeSceneCreator() {
    const deviceSelectors = document.getElementById('deviceSelectors');
    const createSceneBtn = document.getElementById('createSceneBtn');
    const scenePreview = document.getElementById('scenePreview');
    
    if (!deviceSelectors || !createSceneBtn) return;
    
    // Populate device selectors
    devices.forEach(device => {
        const selector = document.createElement('div');
        selector.className = 'device-selector';
        selector.setAttribute('data-id', device.id);
        
        // Choose icon based on device type
        let typeIcon = 'lightbulb';
        if (device.type === 'thermostat') typeIcon = 'temperature-high';
        else if (device.type === 'lock') typeIcon = 'lock';
        else if (device.type === 'camera') typeIcon = 'video';
        
        selector.innerHTML = `
            <div class="selector-icon">
                <i class="fas fa-${typeIcon}"></i>
            </div>
            <div class="selector-info">
                <div class="selector-name">${device.name}</div>
                <div class="selector-type">${device.type}</div>
            </div>
        `;
        
        // Toggle selection on click
        selector.addEventListener('click', () => {
            selector.classList.toggle('selected');
            updateScenePreview();
        });
        
        deviceSelectors.appendChild(selector);
    });
    
    // Create scene button click handler
    createSceneBtn.addEventListener('click', () => {
        const sceneName = document.getElementById('sceneName').value;
        if (!sceneName) {
            showNotification('Error', 'Please enter a scene name');
            return;
        }
        
        const selectedDevices = Array.from(document.querySelectorAll('.device-selector.selected'))
            .map(el => devices.find(d => d.id === parseInt(el.getAttribute('data-id'))));
        
        if (selectedDevices.length === 0) {
            showNotification('Error', 'Please select at least one device');
            return;
        }
        
        // Create the scene (simulation)
        createScene(sceneName, selectedDevices);
    });
    
    function updateScenePreview() {
        const selectedDevices = Array.from(document.querySelectorAll('.device-selector.selected'))
            .map(el => devices.find(d => d.id === parseInt(el.getAttribute('data-id'))));
        
        if (selectedDevices.length === 0) {
            scenePreview.innerHTML = `
                <div class="preview-placeholder">
                    <i class="fas fa-eye"></i>
                    <p>Configure your scene to see a preview</p>
                </div>
            `;
            return;
        }
        
        // Create preview
        scenePreview.innerHTML = `
            <div class="preview-room">
                <div class="preview-title">Scene Preview</div>
                <div class="preview-devices">
                    ${selectedDevices.map(device => `
                        <div class="preview-device">
                            <i class="fas fa-${getIconForType(device.type)}"></i>
                            <span>${device.name}</span>
                            <span class="preview-status">ON</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        
        // Add animation to preview
        document.querySelectorAll('.preview-device').forEach((el, index) => {
            el.style.animation = `fadeIn 0.5s ease-out ${index * 0.1}s forwards`;
            el.style.opacity = '0';
        });
    }
    
    function getIconForType(type) {
        switch(type) {
            case 'light': return 'lightbulb';
            case 'thermostat': return 'temperature-high';
            case 'lock': return 'lock';
            case 'camera': return 'video';
            default: return 'cog';
        }
    }
    
    function createScene(name, devices) {
        // Simulate scene creation
        const scene = {
            id: Date.now(),
            name,
            devices: devices.map(d => d.id)
        };
        
        // Show success notification
        showNotification('Success', `Scene "${name}" created with ${devices.length} devices`);
        
        // Reset form
        document.getElementById('sceneName').value = '';
        document.querySelectorAll('.device-selector.selected').forEach(el => {
            el.classList.remove('selected');
        });
        updateScenePreview();
        
        // Show activation button
        const activateBtn = document.createElement('button');
        activateBtn.className = 'activate-scene-btn';
        activateBtn.innerHTML = '<i class="fas fa-play"></i> Activate Scene';
        activateBtn.addEventListener('click', () => {
            // Simulate scene activation
            showNotification('Scene Activated', `"${name}" scene has been activated`);
            
            // Update device states
            devices.forEach(device => {
                device.status = 'on';
            });
            updateDeviceDisplay();
            saveDevices();
        });
        
        scenePreview.appendChild(activateBtn);
    }
    
    // Add styles for scene preview elements
    const sceneStyles = document.createElement('style');
    sceneStyles.innerHTML = `
        .preview-room {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .preview-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-dark);
        }
        
        .preview-devices {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .preview-device {
            display: flex;
            align-items: center;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            box-shadow: var(--shadow-sm);
        }
        
        .preview-device i {
            margin-right: 15px;
            font-size: 1.2rem;
            color: var(--primary-color);
        }
        
        .preview-status {
            margin-left: auto;
            background-color: var(--success-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .activate-scene-btn {
            margin-top: 20px;
            padding: 15px;
            background-color: var(--success-color);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            width: 100%;
        }
        
        .activate-scene-btn:hover {
            background-color: #2d9a00;
            transform: translateY(-3px);
        }
    `;
    document.head.appendChild(sceneStyles);
} 