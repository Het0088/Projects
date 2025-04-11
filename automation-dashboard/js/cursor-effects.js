// Initialize cursor tracking and enhanced weather
document.addEventListener('DOMContentLoaded', () => {
    initializeCursorTracking();
    enhanceWeatherWidget();
});

// Cursor Tracking Animation
function initializeCursorTracking() {
    const cursor = document.querySelector('.cursor-follower');
    if (!cursor) return;
    
    let mouseX = 0;
    let mouseY = 0;
    let cursorX = 0;
    let cursorY = 0;
    
    // Initialize cursor position off-screen
    cursor.style.opacity = 0;
    
    // Track mouse position
    document.addEventListener('mousemove', (e) => {
        mouseX = e.clientX;
        mouseY = e.clientY;
        
        // Add active class once mouse has moved
        if (!cursor.classList.contains('active')) {
            cursor.classList.add('active');
        }
    });
    
    // Handle mouse enter/leave on interactive elements
    const interactiveElements = document.querySelectorAll('button, a, .device-card, .action-btn, .scene-form input, .voice-button, .device-selector');
    
    interactiveElements.forEach(el => {
        el.addEventListener('mouseenter', () => {
            cursor.classList.add('hover');
        });
        
        el.addEventListener('mouseleave', () => {
            cursor.classList.remove('hover');
        });
    });
    
    // Handle mouse clicks
    document.addEventListener('mousedown', () => {
        cursor.classList.add('click');
    });
    
    document.addEventListener('mouseup', () => {
        cursor.classList.remove('click');
    });
    
    // Smooth animation for cursor follower
    function animateCursor() {
        // Calculate smooth movement with easing
        const easeFactor = 0.15;
        cursorX += (mouseX - cursorX) * easeFactor;
        cursorY += (mouseY - cursorY) * easeFactor;
        
        // Update cursor position
        cursor.style.transform = `translate(${cursorX}px, ${cursorY}px)`;
        
        // Continue animation
        requestAnimationFrame(animateCursor);
    }
    
    // Start animation
    animateCursor();
}

// Enhanced Weather Widget Animations
function enhanceWeatherWidget() {
    const weatherSection = document.querySelector('.weather-widget');
    if (!weatherSection) return;
    
    // Get weather elements
    const currentWeather = document.querySelector('.current-weather');
    const weatherIcon = document.querySelector('.weather-icon');
    const forecastDays = document.querySelectorAll('.forecast-day');
    
    if (!currentWeather || !weatherIcon) return;
    
    // Fetch real weather data for Vadodara, Gujarat
    fetchRealWeatherData();
}

// Fetch real weather data from OpenWeatherMap API
function fetchRealWeatherData() {
    const apiKey = '2177ea14177bec3a1187d7db3fb9a2f3'; // Free API key for demo
    const city = 'Vadodara,Gujarat,IN';
    const weatherUrl = `https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric`;
    const forecastUrl = `https://api.openweathermap.org/data/2.5/forecast?q=${city}&appid=${apiKey}&units=metric`;
    
    // Fetch current weather
    fetch(weatherUrl)
        .then(response => response.json())
        .then(data => {
            updateCurrentWeather(data);
        })
        .catch(error => {
            console.error('Error fetching current weather:', error);
            // Fall back to default values if API fails
            setDefaultWeather();
        });
    
    // Fetch forecast
    fetch(forecastUrl)
        .then(response => response.json())
        .then(data => {
            updateForecast(data);
        })
        .catch(error => {
            console.error('Error fetching forecast:', error);
            // Keep existing forecast if API fails
        });
}

// Update current weather with real data
function updateCurrentWeather(data) {
    const weatherIcon = document.querySelector('.weather-icon');
    const temperature = document.querySelector('.temperature');
    const condition = document.querySelector('.condition');
    
    if (!weatherIcon || !temperature || !condition) return;
    
    // Clear existing classes
    weatherIcon.className = 'weather-icon';
    
    // Get weather condition
    const weatherCode = data.weather[0].id;
    const weatherType = getWeatherTypeFromCode(weatherCode);
    weatherIcon.classList.add(weatherType);
    
    // Update icon
    weatherIcon.innerHTML = getWeatherIcon(weatherType);
    
    // Update temperature (rounded to nearest integer)
    const temp = Math.round(data.main.temp);
    temperature.innerHTML = `${temp}<div class="temperature-change">
        <i class="fas fa-arrow-${data.main.temp > 20 ? 'up' : 'down'}"></i>${Math.round(data.main.feels_like - temp)}°C
    </div>°C`;
    
    // Update condition
    condition.textContent = data.weather[0].main;
    
    // Add weather effects based on type
    const currentWeather = document.querySelector('.current-weather');
    if (currentWeather) {
        // Remove any existing effects
        const existingRays = currentWeather.querySelector('.sun-rays');
        if (existingRays) existingRays.remove();
        
        const existingRain = currentWeather.querySelector('.rain-container');
        if (existingRain) existingRain.remove();
        
        // Add new effects
        if (weatherType === 'sunny') {
            addSunRays(currentWeather);
        } else if (weatherType === 'rainy') {
            addRaindrops(currentWeather);
        }
    }
}

// Update forecast with real data
function updateForecast(data) {
    const forecastDays = document.querySelectorAll('.forecast-day');
    
    // Get daily forecasts (OpenWeatherMap free API returns 3-hour forecasts)
    // We'll pick data points for the next 4 days at around noon
    const dailyForecasts = [];
    const today = new Date().getDay();
    const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    
    // Find one forecast per day around noon
    let currentDay = -1;
    
    for (let i = 0; i < data.list.length && dailyForecasts.length < 4; i++) {
        const forecastTime = new Date(data.list[i].dt * 1000);
        const forecastDay = forecastTime.getDay();
        
        // Skip today and get unique days
        if (forecastDay !== today && forecastDay !== currentDay && forecastTime.getHours() >= 11 && forecastTime.getHours() <= 14) {
            dailyForecasts.push(data.list[i]);
            currentDay = forecastDay;
        }
    }
    
    // Update UI with forecast data
    forecastDays.forEach((day, index) => {
        if (index < dailyForecasts.length) {
            const forecast = dailyForecasts[index];
            const dayName = day.querySelector('.day-name');
            const dayIcon = day.querySelector('.day-icon');
            const dayTemp = day.querySelector('.day-temp');
            
            if (dayName && dayIcon && dayTemp) {
                // Update day name
                const forecastDate = new Date(forecast.dt * 1000);
                dayName.textContent = daysOfWeek[forecastDate.getDay()];
                
                // Update icon
                const weatherCode = forecast.weather[0].id;
                const weatherType = getWeatherTypeFromCode(weatherCode);
                dayIcon.innerHTML = getWeatherIcon(weatherType);
                
                // Update temperature
                const temp = Math.round(forecast.main.temp);
                dayTemp.textContent = `${temp}°C`;
                
                // Update min-max
                const minMaxDiv = day.querySelector('.min-max');
                if (minMaxDiv) {
                    minMaxDiv.remove(); // Remove existing min-max
                }
                
                const newMinMaxDiv = document.createElement('div');
                newMinMaxDiv.className = 'min-max';
                // Use temp_min and temp_max from the API data
                const minTemp = Math.round(forecast.main.temp_min);
                const maxTemp = Math.round(forecast.main.temp_max);
                newMinMaxDiv.innerHTML = `<span>${minTemp}°C</span><span>/${maxTemp}°C</span>`;
                
                dayTemp.insertAdjacentElement('afterend', newMinMaxDiv);
            }
        }
    });
}

// Map OpenWeatherMap codes to our weather types
function getWeatherTypeFromCode(code) {
    // Weather condition codes: https://openweathermap.org/weather-conditions
    if (code >= 200 && code < 300) return 'stormy'; // Thunderstorm
    if (code >= 300 && code < 400) return 'rainy'; // Drizzle
    if (code >= 500 && code < 600) return 'rainy'; // Rain
    if (code >= 600 && code < 700) return 'snowy'; // Snow
    if (code >= 700 && code < 800) return 'foggy'; // Atmosphere (fog, haze)
    if (code === 800) return 'sunny'; // Clear
    if (code > 800) return 'cloudy'; // Clouds
    
    return 'sunny'; // Default
}

// Set default weather if API fails
function setDefaultWeather() {
    const weatherIcon = document.querySelector('.weather-icon');
    if (!weatherIcon) return;
    
    // Clear existing classes and add default
    weatherIcon.className = 'weather-icon';
    weatherIcon.classList.add('sunny');
    
    // Set default icon
    weatherIcon.innerHTML = getWeatherIcon('sunny');
    
    // Add default effects
    const currentWeather = document.querySelector('.current-weather');
    if (currentWeather) {
        // Remove any existing effects
        const existingRays = currentWeather.querySelector('.sun-rays');
        if (existingRays) existingRays.remove();
        
        // Add sun rays as default
        addSunRays(currentWeather);
    }
}

// Get weather icon based on type
function getWeatherIcon(type) {
    switch(type) {
        case 'sunny':
            return '<i class="fas fa-sun"></i>';
        case 'cloudy':
            return '<i class="fas fa-cloud"></i>';
        case 'rainy':
            return '<i class="fas fa-cloud-rain"></i>';
        case 'stormy':
            return '<i class="fas fa-bolt"></i>';
        case 'snowy':
            return '<i class="fas fa-snowflake"></i>';
        case 'foggy':
            return '<i class="fas fa-smog"></i>';
        default:
            return '<i class="fas fa-sun"></i>';
    }
}

// Add sun rays effect
function addSunRays(container) {
    const raysContainer = document.createElement('div');
    raysContainer.className = 'sun-rays';
    
    // Create multiple rays at different angles
    for (let i = 0; i < 12; i++) {
        const ray = document.createElement('div');
        ray.className = 'ray';
        
        // Position and rotate the ray
        const angle = i * 30;
        const delay = i * 0.2;
        
        ray.style.top = '80px';
        ray.style.left = '100px';
        ray.style.transform = `rotate(${angle}deg)`;
        ray.style.animation = `rayRotate 10s linear ${delay}s infinite`;
        
        raysContainer.appendChild(ray);
    }
    
    container.appendChild(raysContainer);
}

// Add raindrops effect
function addRaindrops(container) {
    const rainContainer = document.createElement('div');
    rainContainer.className = 'rain-container';
    
    // Create multiple raindrops
    for (let i = 0; i < 40; i++) {
        const raindrop = document.createElement('div');
        raindrop.className = 'raindrop';
        
        // Randomize position and animation
        const left = Math.random() * 100;
        const delay = Math.random() * 2;
        const duration = Math.random() * 1 + 1;
        
        raindrop.style.left = `${left}%`;
        raindrop.style.animation = `rainFall ${duration}s linear ${delay}s infinite`;
        
        rainContainer.appendChild(raindrop);
    }
    
    container.appendChild(rainContainer);
} 