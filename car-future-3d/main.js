// Import necessary Three.js modules
import * as THREE from 'https://cdn.skypack.dev/three@0.136.0';
import { GLTFLoader } from 'https://cdn.skypack.dev/three@0.136.0/examples/jsm/loaders/GLTFLoader.js';
import { OrbitControls } from 'https://cdn.skypack.dev/three@0.136.0/examples/jsm/controls/OrbitControls.js';
import { RGBELoader } from 'https://cdn.skypack.dev/three@0.136.0/examples/jsm/loaders/RGBELoader.js';
import { gsap } from 'https://cdn.skypack.dev/gsap@3.9.1';
import { DRACOLoader } from 'https://cdn.skypack.dev/three@0.136.0/examples/jsm/loaders/DRACOLoader.js';

// DOM Elements
const loadingScreen = document.querySelector('.loading-screen');
const loadingBar = document.querySelector('.loading-bar');
const loadingText = document.querySelector('.loading-text');
const skipLoadingButton = document.getElementById('skip-loading');
const carModelContainer = document.getElementById('car-model-container');
const interactiveModelContainer = document.getElementById('interactive-model-container');
const exploreButton = document.querySelector('.primary-button');
const galleryGrid = document.querySelector('.gallery-grid');

// Global variables
let mainScene, mainCamera, mainRenderer, mainControls;
let interactiveScene, interactiveCamera, interactiveRenderer, interactiveControls;
let carModel, interactiveCarModel;
let mixer, animations;
let currentColorIndex = 0;
const carColors = [
  new THREE.Color(0x00f0ff), // Cyan
  new THREE.Color(0xff00aa), // Magenta
  new THREE.Color(0xffffff), // White
  new THREE.Color(0x000000), // Black
  new THREE.Color(0xff6600)  // Orange
];

// Car model URLs - using free sources
const CAR_MODEL_URL = 'https://threejs.org/examples/models/gltf/ferrari.glb'; // Ferrari model from Three.js examples
// Alternative models (for fallbacks)
const ALTERNATIVE_CAR_MODELS = [
  'https://market-assets.fra1.cdn.digitaloceanspaces.com/market-assets/models/tesla-model-3/model.gltf', // Tesla Model 3
  'https://vazxmixjsiawhamofees.supabase.co/storage/v1/object/public/models/tesla-cybertruck/model.gltf' // Tesla Cybertruck
];
// Fallback to a simple model if the car doesn't load
const FALLBACK_MODEL_URL = 'https://raw.githubusercontent.com/KhronosGroup/glTF-Sample-Models/master/2.0/Box/glTF/Box.gltf';

// Initialize loading
let resourcesLoaded = 0;
const totalResources = 3; // Model, HDR environment, textures
let modelLoadingFailed = false;

// Function to update loading progress
function updateLoadingProgress(increment = 1) {
  resourcesLoaded += increment;
  const progress = (resourcesLoaded / totalResources) * 100;
  loadingBar.style.width = `${progress}%`;
  loadingText.textContent = `Loading Experience... ${Math.floor(progress)}%`;
  
  // Force complete loading if taking too long (after 10 seconds)
  if (progress >= 66 && !window.loadingTimeoutSet) {
    window.loadingTimeoutSet = true;
    setTimeout(() => {
      if (resourcesLoaded < totalResources) {
        console.log("Forcing loading completion after timeout");
        resourcesLoaded = totalResources;
        completeLoading();
      }
    }, 5000);
  }
  
  if (resourcesLoaded >= totalResources) {
    completeLoading();
  }
}

function completeLoading() {
  // Loading complete
  gsap.to(loadingScreen, {
    opacity: 0,
    duration: 1,
    delay: 0.5,
    onComplete: () => {
      loadingScreen.style.display = 'none';
      animateEntrance();
    }
  });
}

// Initialize the main scene (hero section)
function initMainScene() {
  // Create scene
  mainScene = new THREE.Scene();
  mainScene.background = new THREE.Color(0x050510);
  
  // Create camera
  mainCamera = new THREE.PerspectiveCamera(40, carModelContainer.clientWidth / carModelContainer.clientHeight, 0.1, 1000);
  mainCamera.position.set(5, 2, 8);
  
  // Create renderer
  mainRenderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
  mainRenderer.setSize(carModelContainer.clientWidth, carModelContainer.clientHeight);
  mainRenderer.setPixelRatio(window.devicePixelRatio);
  mainRenderer.outputEncoding = THREE.sRGBEncoding;
  mainRenderer.toneMapping = THREE.ACESFilmicToneMapping;
  mainRenderer.toneMappingExposure = 1.2;
  mainRenderer.shadowMap.enabled = true;
  mainRenderer.shadowMap.type = THREE.PCFSoftShadowMap;
  carModelContainer.appendChild(mainRenderer.domElement);
  
  // Add lights
  const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
  mainScene.add(ambientLight);
  
  const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
  directionalLight.position.set(5, 10, 7.5);
  directionalLight.castShadow = true;
  directionalLight.shadow.mapSize.width = 2048;
  directionalLight.shadow.mapSize.height = 2048;
  mainScene.add(directionalLight);
  
  // Add rim light for dramatic effect
  const rimLight = new THREE.SpotLight(0x00f0ff, 2);
  rimLight.position.set(-5, 2, -5);
  rimLight.angle = Math.PI / 6;
  rimLight.penumbra = 0.3;
  rimLight.castShadow = true;
  rimLight.shadow.mapSize.width = 1024;
  rimLight.shadow.mapSize.height = 1024;
  mainScene.add(rimLight);
  
  // Add environment map
  const rgbeLoader = new RGBELoader();
  rgbeLoader.load('https://threejs.org/examples/textures/equirectangular/venice_sunset_1k.hdr', 
    // Success callback
    function(texture) {
      texture.mapping = THREE.EquirectangularReflectionMapping;
      mainScene.environment = texture;
      updateLoadingProgress();
    },
    // Progress callback
    undefined,
    // Error callback
    function(error) {
      console.error('Error loading HDR environment:', error);
      // Create a default environment
      const envMap = new THREE.CubeTextureLoader()
        .load([
          'https://threejs.org/examples/textures/cube/Park2/posx.jpg',
          'https://threejs.org/examples/textures/cube/Park2/negx.jpg',
          'https://threejs.org/examples/textures/cube/Park2/posy.jpg',
          'https://threejs.org/examples/textures/cube/Park2/negy.jpg',
          'https://threejs.org/examples/textures/cube/Park2/posz.jpg',
          'https://threejs.org/examples/textures/cube/Park2/negz.jpg',
        ]);
      mainScene.environment = envMap;
      updateLoadingProgress();
    }
  );
  
  // Load car model
  loadCarModel();
  
  // Add a rotating platform
  const platformGeometry = new THREE.CylinderGeometry(3, 3, 0.1, 64);
  const platformMaterial = new THREE.MeshStandardMaterial({
    color: 0x111122,
    metalness: 0.8,
    roughness: 0.2,
    envMapIntensity: 1.5
  });
  const platform = new THREE.Mesh(platformGeometry, platformMaterial);
  platform.position.y = -0.55;
  platform.receiveShadow = true;
  mainScene.add(platform);
  
  // Add a subtle animation to the platform
  gsap.to(platform.rotation, {
    y: Math.PI * 2,
    duration: 20,
    repeat: -1,
    ease: 'none'
  });
  
  // Add post-processing effects
  updateLoadingProgress();
}

// Function to load car model with fallback
function loadCarModel() {
  // Initialize DRACO loader for compressed models
  const dracoLoader = new DRACOLoader();
  dracoLoader.setDecoderPath('https://www.gstatic.com/draco/versioned/decoders/1.4.1/');
  
  const gltfLoader = new GLTFLoader();
  gltfLoader.setDRACOLoader(dracoLoader);
  
  // Try to load the main model
  gltfLoader.load(
    CAR_MODEL_URL, 
    function(gltf) {
      setupCarModel(gltf);
    },
    // Progress callback
    function(xhr) {
      console.log((xhr.loaded / xhr.total * 100) + '% loaded');
    },
    // Error callback
    function(error) {
      console.error('Error loading primary car model:', error);
      console.log('Trying alternative models...');
      
      // Try alternative models sequentially
      tryLoadAlternativeModels(0);
    }
  );
  
  // Function to try loading alternative models
  function tryLoadAlternativeModels(index) {
    if (index >= ALTERNATIVE_CAR_MODELS.length) {
      console.error('All alternative models failed to load. Using fallback cube.');
      createFallbackCube();
      return;
    }
    
    gltfLoader.load(
      ALTERNATIVE_CAR_MODELS[index],
      function(gltf) {
        console.log('Successfully loaded alternative model:', ALTERNATIVE_CAR_MODELS[index]);
        setupCarModel(gltf);
      },
      undefined,
      function(error) {
        console.error(`Error loading alternative model ${index}:`, error);
        tryLoadAlternativeModels(index + 1);
      }
    );
  }
}

// Setup the car model
function setupCarModel(gltf) {
  carModel = gltf.scene;
  
  // Cars have different scales and orientations, so adjust based on the model
  let modelName = CAR_MODEL_URL.split('/').pop();
  
  // Default settings
  let scale = 1.5;
  let posY = -0.5;
  let rotY = Math.PI / 4;
  
  // Model specific adjustments
  if (modelName.includes('ferrari')) {
    scale = 1.5;
    posY = -0.55;
    rotY = Math.PI / 4;
    
    // Add a slight tilt to the model for dynamic effect
    carModel.rotation.x = -0.05;
  } else if (modelName.includes('tesla-model-3')) {
    scale = 0.4;
    posY = -0.3;
    rotY = Math.PI / 4;
  } else if (modelName.includes('cybertruck')) {
    scale = 0.8;
    posY = -0.4;
    rotY = Math.PI / 4;
  }
  
  // Apply scale, position and rotation
  carModel.scale.set(scale, scale, scale);
  carModel.position.set(0, posY, 0);
  carModel.rotation.y = rotY;
  
  // Apply materials
  carModel.traverse((child) => {
    if (child.isMesh && child.material) {
      // For car body parts (usually the largest parts or specific materials)
      if (
        child.name.toLowerCase().includes('body') || 
        child.name.toLowerCase().includes('car') ||
        child.name.toLowerCase().includes('chassis') ||
        child.name.toLowerCase().includes('frame') ||
        child.geometry.attributes.position.count > 5000 // Assume large meshes are body panels
      ) {
        // Store original color for reverting
        if (!child.userData.originalColor && child.material.color) {
          child.userData.originalColor = child.material.color.clone();
        }
        
        // Apply car color
        child.material.color = carColors[currentColorIndex];
        child.material.metalness = 0.9;
        child.material.roughness = 0.1;
      }
      
      // For glass parts
      if (
        child.name.toLowerCase().includes('glass') || 
        child.name.toLowerCase().includes('window')
      ) {
        child.material.transparent = true;
        child.material.opacity = 0.8;
        child.material.metalness = 0.9;
        child.material.roughness = 0.1;
      }
      
      // For wheels
      if (
        child.name.toLowerCase().includes('wheel') || 
        child.name.toLowerCase().includes('tire')
      ) {
        child.material.metalness = 0.5;
        child.material.roughness = 0.7;
      }
      
      // Enhance material properties for all parts
      child.material.envMapIntensity = 1.5;
      child.castShadow = true;
      child.receiveShadow = true;
    }
  });
  
  // Add to scene
  mainScene.add(carModel);
  updateLoadingProgress();
  
  // Initialize interactive scene once the model is loaded
  initInteractiveScene();
  
  // Setup animations if available
  if (gltf.animations && gltf.animations.length) {
    mixer = new THREE.AnimationMixer(carModel);
    animations = gltf.animations;
    
    // Play the first animation
    const action = mixer.clipAction(animations[0]);
    action.play();
  } else {
    // Add a subtle hover animation if no animations exist
    gsap.to(carModel.position, {
      y: posY + 0.05,
      duration: 2,
      repeat: -1,
      yoyo: true,
      ease: 'power1.inOut'
    });
  }
}

// Create a fallback cube if all models fail to load
function createFallbackCube() {
  console.log('Creating fallback cube');
  carModel = new THREE.Group();
  
  // Create a car-like shape using primitive geometries
  const bodyGeometry = new THREE.BoxGeometry(2, 0.75, 4);
  const bodyMaterial = new THREE.MeshStandardMaterial({
    color: carColors[currentColorIndex],
    metalness: 0.7,
    roughness: 0.2,
    envMapIntensity: 1.5
  });
  const body = new THREE.Mesh(bodyGeometry, bodyMaterial);
  body.position.y = 0.5;
  body.castShadow = true;
  carModel.add(body);
  
  // Add cabin
  const cabinGeometry = new THREE.BoxGeometry(1.7, 0.6, 2);
  const cabinMaterial = new THREE.MeshStandardMaterial({
    color: 0x111111,
    metalness: 0.7,
    roughness: 0.2,
    transparent: true,
    opacity: 0.7,
    envMapIntensity: 1.5
  });
  const cabin = new THREE.Mesh(cabinGeometry, cabinMaterial);
  cabin.position.y = 1.15;
  cabin.position.z = -0.5;
  cabin.castShadow = true;
  carModel.add(cabin);
  
  // Add wheels
  const wheelGeometry = new THREE.CylinderGeometry(0.4, 0.4, 0.15, 32);
  const wheelMaterial = new THREE.MeshStandardMaterial({
    color: 0x333333,
    metalness: 0.5,
    roughness: 0.7
  });
  
  // Front left wheel
  const wheelFL = new THREE.Mesh(wheelGeometry, wheelMaterial);
  wheelFL.rotation.z = Math.PI / 2;
  wheelFL.position.set(-1.1, 0.4, 1.2);
  carModel.add(wheelFL);
  
  // Front right wheel
  const wheelFR = new THREE.Mesh(wheelGeometry, wheelMaterial);
  wheelFR.rotation.z = Math.PI / 2;
  wheelFR.position.set(1.1, 0.4, 1.2);
  carModel.add(wheelFR);
  
  // Rear left wheel
  const wheelRL = new THREE.Mesh(wheelGeometry, wheelMaterial);
  wheelRL.rotation.z = Math.PI / 2;
  wheelRL.position.set(-1.1, 0.4, -1.2);
  carModel.add(wheelRL);
  
  // Rear right wheel
  const wheelRR = new THREE.Mesh(wheelGeometry, wheelMaterial);
  wheelRR.rotation.z = Math.PI / 2;
  wheelRR.position.set(1.1, 0.4, -1.2);
  carModel.add(wheelRR);
  
  // Add headlights
  const headlightGeometry = new THREE.CylinderGeometry(0.1, 0.1, 0.1, 16);
  const headlightMaterial = new THREE.MeshStandardMaterial({
    color: 0xffffff,
    emissive: 0xffffff,
    emissiveIntensity: 1,
    metalness: 0.9,
    roughness: 0.1
  });
  
  // Left headlight
  const headlightL = new THREE.Mesh(headlightGeometry, headlightMaterial);
  headlightL.rotation.z = Math.PI / 2;
  headlightL.position.set(-0.7, 0.5, 2);
  carModel.add(headlightL);
  
  // Right headlight
  const headlightR = new THREE.Mesh(headlightGeometry, headlightMaterial);
  headlightR.rotation.z = Math.PI / 2;
  headlightR.position.set(0.7, 0.5, 2);
  carModel.add(headlightR);
  
  // Position and scale the model
  carModel.scale.set(1.5, 1.5, 1.5);
  carModel.position.y = -0.5;
  carModel.rotation.y = Math.PI / 4;
  
  mainScene.add(carModel);
  updateLoadingProgress();
  
  // Add a subtle hover animation
  gsap.to(carModel.position, {
    y: -0.45,
    duration: 2,
    repeat: -1,
    yoyo: true,
    ease: 'power1.inOut'
  });
}

// Function to initialize the interactive scene (for the 360 view)
function initInteractiveScene() {
  interactiveScene = new THREE.Scene();
  interactiveScene.background = new THREE.Color(0x050510);
  
  // Create camera
  interactiveCamera = new THREE.PerspectiveCamera(
    50, 
    interactiveModelContainer.clientWidth / interactiveModelContainer.clientHeight, 
    0.1, 
    1000
  );
  interactiveCamera.position.set(5, 2, 8);
  
  // Create renderer
  interactiveRenderer = new THREE.WebGLRenderer({ antialias: true });
  interactiveRenderer.setSize(interactiveModelContainer.clientWidth, interactiveModelContainer.clientHeight);
  interactiveRenderer.setPixelRatio(window.devicePixelRatio);
  interactiveRenderer.outputEncoding = THREE.sRGBEncoding;
  interactiveRenderer.toneMapping = THREE.ACESFilmicToneMapping;
  interactiveRenderer.toneMappingExposure = 1.2;
  interactiveRenderer.shadowMap.enabled = true;
  interactiveRenderer.shadowMap.type = THREE.PCFSoftShadowMap;
  interactiveModelContainer.appendChild(interactiveRenderer.domElement);
  
  // Add lights
  const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
  interactiveScene.add(ambientLight);
  
  const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
  directionalLight.position.set(5, 10, 7.5);
  directionalLight.castShadow = true;
  directionalLight.shadow.mapSize.width = 2048;
  directionalLight.shadow.mapSize.height = 2048;
  interactiveScene.add(directionalLight);
  
  // Add environment map (reuse from main scene if available)
  if (mainScene && mainScene.environment) {
    interactiveScene.environment = mainScene.environment;
  } else {
    // Create a default environment
    const envMap = new THREE.CubeTextureLoader()
      .load([
        'https://threejs.org/examples/textures/cube/Park2/posx.jpg',
        'https://threejs.org/examples/textures/cube/Park2/negx.jpg',
        'https://threejs.org/examples/textures/cube/Park2/posy.jpg',
        'https://threejs.org/examples/textures/cube/Park2/negy.jpg',
        'https://threejs.org/examples/textures/cube/Park2/posz.jpg',
        'https://threejs.org/examples/textures/cube/Park2/negz.jpg',
      ]);
    interactiveScene.environment = envMap;
  }
  
  // Clone the car model for the interactive view
  if (carModel) {
    interactiveCarModel = carModel.clone();
    // Center the model 
    interactiveCarModel.position.set(0, 0, 0);
    interactiveScene.add(interactiveCarModel);
    
    // Add a platform
    const platformGeometry = new THREE.CylinderGeometry(4, 4, 0.1, 64);
    const platformMaterial = new THREE.MeshStandardMaterial({
      color: 0x111122,
      metalness: 0.8,
      roughness: 0.2,
      envMapIntensity: 1.5
    });
    const platform = new THREE.Mesh(platformGeometry, platformMaterial);
    platform.position.y = -0.55;
    platform.receiveShadow = true;
    interactiveScene.add(platform);
  } else {
    console.error('No car model available to clone for interactive view');
  }
  
  // Add orbit controls for interactive view
  interactiveControls = new OrbitControls(interactiveCamera, interactiveRenderer.domElement);
  interactiveControls.enableDamping = true;
  interactiveControls.dampingFactor = 0.05;
  interactiveControls.minDistance = 3;
  interactiveControls.maxDistance = 15;
  interactiveControls.maxPolarAngle = Math.PI / 2 + 0.3; // Limit viewing angle to prevent seeing under the platform
  interactiveControls.target.set(0, 0, 0);
  interactiveControls.update();
}

// Function to change car color
function changeCarColor(index) {
  if (!carModel) return;
  
  currentColorIndex = index;
  
  // Update main model
  carModel.traverse((child) => {
    if (child.isMesh && child.material) {
      if (
        child.name.toLowerCase().includes('body') || 
        child.name.toLowerCase().includes('car') ||
        child.name.toLowerCase().includes('chassis') ||
        child.name.toLowerCase().includes('frame') ||
        child.geometry.attributes.position.count > 5000 ||
        child === carModel.getObjectByName('body') // For fallback model
      ) {
        child.material.color = carColors[currentColorIndex];
      }
    }
  });
  
  // Update interactive model if it exists
  if (interactiveCarModel) {
    interactiveCarModel.traverse((child) => {
      if (child.isMesh && child.material) {
        if (
          child.name.toLowerCase().includes('body') || 
          child.name.toLowerCase().includes('car') ||
          child.name.toLowerCase().includes('chassis') ||
          child.name.toLowerCase().includes('frame') ||
          child.geometry.attributes.position.count > 5000 ||
          child === interactiveCarModel.getObjectByName('body') // For fallback model
        ) {
          child.material.color = carColors[currentColorIndex];
        }
      }
    });
  }
  
  // Update the active color swatch in UI
  updateActiveColorSwatch(index);
}

function updateActiveColorSwatch(index) {
  // Update UI color swatches if they exist
  const colorSwatches = document.querySelectorAll('.color-swatch');
  if (colorSwatches.length) {
    colorSwatches.forEach((swatch, i) => {
      if (i === index) {
        swatch.classList.add('active');
      } else {
        swatch.classList.remove('active');
      }
    });
  }
}

// Animation entrance
function animateEntrance() {
  if (!carModel) return;
  
  // Reset car position
  gsap.set(carModel.position, { x: -10, y: -0.5, opacity: 0 });
  
  // Animate car entrance
  gsap.to(carModel.position, {
    x: 0,
    duration: 1.5,
    ease: 'power2.out'
  });
  
  // Animate car rotation
  gsap.to(carModel.rotation, {
    y: Math.PI * 2 + Math.PI / 4,
    duration: 2,
    ease: 'power2.out'
  });
  
  // Reveal headings with stagger
  gsap.from('.hero-content h1, .hero-content h2, .hero-content p, .hero-content .buttons', {
    y: 50,
    opacity: 0,
    duration: 1,
    stagger: 0.2,
    ease: 'power2.out'
  });
}

// Animation loop
function animate() {
  requestAnimationFrame(animate);
  
  // Update animations
  if (mixer) {
    mixer.update(0.016); // Assume 60fps
  }
  
  // Update controls
  if (interactiveControls) {
    interactiveControls.update();
  }
  
  // Render scenes
  if (mainRenderer && mainScene && mainCamera) {
    mainRenderer.render(mainScene, mainCamera);
  }
  
  if (interactiveRenderer && interactiveScene && interactiveCamera && document.body.contains(interactiveModelContainer)) {
    interactiveRenderer.render(interactiveScene, interactiveCamera);
  }
}

// Handle color selection
function setupColorSelectors() {
  const colorContainer = document.querySelector('.color-options');
  if (!colorContainer) return;
  
  // Clear existing swatches
  colorContainer.innerHTML = '';
  
  // Create color swatches
  carColors.forEach((color, index) => {
    const swatch = document.createElement('div');
    swatch.classList.add('color-swatch');
    if (index === currentColorIndex) {
      swatch.classList.add('active');
    }
    
    swatch.style.backgroundColor = `#${color.getHexString()}`;
    swatch.addEventListener('click', () => changeCarColor(index));
    colorContainer.appendChild(swatch);
  });
}

// Handle window resize
function handleResize() {
  // Update main scene
  if (mainCamera && mainRenderer && carModelContainer) {
    mainCamera.aspect = carModelContainer.clientWidth / carModelContainer.clientHeight;
    mainCamera.updateProjectionMatrix();
    mainRenderer.setSize(carModelContainer.clientWidth, carModelContainer.clientHeight);
  }
  
  // Update interactive scene
  if (interactiveCamera && interactiveRenderer && interactiveModelContainer) {
    interactiveCamera.aspect = interactiveModelContainer.clientWidth / interactiveModelContainer.clientHeight;
    interactiveCamera.updateProjectionMatrix();
    interactiveRenderer.setSize(interactiveModelContainer.clientWidth, interactiveModelContainer.clientHeight);
  }
}

// Functions to initialize scene elements
function initializeSections() {
  // Setup color selectors
  setupColorSelectors();
  
  // Add scroll animations for sections
  document.querySelectorAll('section').forEach(section => {
    gsap.from(section, {
      scrollTrigger: {
        trigger: section,
        start: "top 80%",
        toggleActions: "play none none none"
      },
      opacity: 0,
      y: 50,
      duration: 0.8,
      ease: "power3.out"
    });
  });
  
  // Handle images error loading
  document.querySelectorAll('.gallery-item img').forEach(img => {
    img.addEventListener('error', function() {
      // Replace with a placeholder if the image fails to load
      this.src = 'https://via.placeholder.com/800x450?text=NEXUS+Gallery+Image';
    });
  });
}

// Initialize event listeners
function initEventListeners() {
  // Skip loading button
  if (skipLoadingButton) {
    skipLoadingButton.addEventListener('click', () => {
      resourcesLoaded = totalResources;
      completeLoading();
    });
  }
  
  // Explore button to show interactive model
  if (exploreButton) {
    exploreButton.addEventListener('click', () => {
      // Initialize interactive scene if not already
      if (!interactiveScene) {
        initInteractiveScene();
      }
      
      // Show interactive model container
      interactiveModelContainer.style.display = 'block';
      interactiveModelContainer.style.opacity = '0';
      
      // Animate transition
      gsap.to(interactiveModelContainer, {
        opacity: 1,
        duration: 0.5,
        onComplete: () => {
          // Update controls and renderer once visible
          if (interactiveControls) {
            interactiveControls.update();
          }
        }
      });
      
      // Add close button if not exists
      if (!document.querySelector('.close-interactive')) {
        const closeBtn = document.createElement('button');
        closeBtn.className = 'close-interactive';
        closeBtn.innerHTML = '&times;';
        closeBtn.addEventListener('click', () => {
          gsap.to(interactiveModelContainer, {
            opacity: 0,
            duration: 0.5,
            onComplete: () => {
              interactiveModelContainer.style.display = 'none';
            }
          });
        });
        interactiveModelContainer.appendChild(closeBtn);
      }
    });
  }
  
  // Window resize event
  window.addEventListener('resize', handleResize);
}

// Scroll-based animations
function initScrollAnimations() {
  const sections = document.querySelectorAll('section');
  
  // Create intersection observer
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('fade-in');
        
        // Add specific animations based on section
        if (entry.target.classList.contains('features')) {
          const cards = entry.target.querySelectorAll('.feature-card');
          gsap.from(cards, {
            y: 50,
            opacity: 0,
            duration: 0.8,
            stagger: 0.1,
            ease: 'power2.out'
          });
        }
        
        if (entry.target.classList.contains('specifications')) {
          const specs = entry.target.querySelectorAll('.spec-category');
          gsap.from(specs, {
            x: -50,
            opacity: 0,
            duration: 0.8,
            stagger: 0.2,
            ease: 'power2.out'
          });
        }
      }
    });
  }, { threshold: 0.1 });
  
  // Observe all sections
  sections.forEach(section => {
    observer.observe(section);
  });
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  // Initialize main scene
  initMainScene();
  
  // Start animation loop
  animate();
  
  // Initialize event listeners
  initEventListeners();
  
  // Handle all images in the document
  document.querySelectorAll('img').forEach(img => {
    img.addEventListener('error', function() {
      // Replace with a placeholder if the image fails to load
      if (this.classList.contains('gallery-item img')) {
        this.src = 'https://via.placeholder.com/800x450?text=NEXUS+Gallery+Image';
      } else if (this.parentNode.classList.contains('specs-image')) {
        this.src = 'https://via.placeholder.com/800x450?text=NEXUS+Technical+Image';
      } else {
        this.src = 'https://via.placeholder.com/800x450?text=NEXUS+Image';
      }
      console.log('Image failed to load, replaced with placeholder:', this.alt);
    });
  });
  
  // Initialize UI sections once loaded
  window.addEventListener('load', () => {
    initializeSections();
    initScrollAnimations();
    
    // Force car model to be visible if it's not showing after load
    setTimeout(() => {
      const modelContainer = document.getElementById('car-model-container');
      const interactiveContainer = document.getElementById('interactive-model-container');
      
      if (modelContainer && (!carModel || modelContainer.children.length === 0)) {
        console.log('Forcing fallback car model creation');
        createFallbackCube();
      }
      
      if (interactiveContainer && (!interactiveCarModel || interactiveContainer.children.length === 0)) {
        console.log('Forcing interactive scene initialization');
        initInteractiveScene();
      }
    }, 5000); // Check after 5 seconds
  });
}); 