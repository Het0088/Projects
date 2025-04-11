// Import necessary Three.js modules
import * as THREE from 'https://cdn.skypack.dev/three@0.136.0';
import { GLTFLoader } from 'https://cdn.skypack.dev/three@0.136.0/examples/jsm/loaders/GLTFLoader.js';
import { OrbitControls } from 'https://cdn.skypack.dev/three@0.136.0/examples/jsm/controls/OrbitControls.js';
import { RGBELoader } from 'https://cdn.skypack.dev/three@0.136.0/examples/jsm/loaders/RGBELoader.js';
import { gsap } from 'https://cdn.skypack.dev/gsap@3.9.1';
import { Water } from 'https://cdn.skypack.dev/three@0.136.0/examples/jsm/objects/Water.js';

// DOM Elements
const loadingScreen = document.querySelector('.loading-screen');
const loadingBar = document.querySelector('.loading-bar');
const loadingText = document.querySelector('.loading-text');
const skipLoadingButton = document.getElementById('skip-loading');
const drinkModelContainer = document.getElementById('drink-model-container');
const interactiveDrinkContainer = document.getElementById('interactive-drink-container');
const exploreButton = document.querySelector('.primary-button');
const galleryGrid = document.querySelector('.gallery-grid');

// Global variables
let mainScene, mainCamera, mainRenderer, mainControls;
let interactiveScene, interactiveCamera, interactiveRenderer, interactiveControls;
let drinkModel, interactiveDrinkModel;
let mixer, animations;
let currentColorIndex = 0;
const drinkColors = [
  new THREE.Color(0x00bfff), // Blue
  new THREE.Color(0xff4500), // Orange
  new THREE.Color(0xffffff), // White
  new THREE.Color(0x000000), // Black
];
let waterDroplets = [];
let clock = new THREE.Clock();

// Drink model URL - Using a reliable can model
const DRINK_MODEL_URL = 'https://cdn.glitch.global/3e633c04-a96d-4e45-a31a-5df76038e8d2/soda_can.glb?v=1678123966847';
// Fallback model in case the primary one fails
const FALLBACK_MODEL_URL = 'https://raw.githubusercontent.com/KhronosGroup/glTF-Sample-Models/master/2.0/SodaCan/glTF/SodaCan.gltf';

// HDR environment URL
const HDR_ENVIRONMENT_URL = 'https://raw.githubusercontent.com/KhronosGroup/glTF-Sample-Models/master/2.0/BoxAnimated/hdr/BoxAnimated.hdr';

// Initialize loading
let resourcesLoaded = 0;
const totalResources = 2; // Environment and water droplets

// Function to update loading progress
function updateLoadingProgress(increment = 1) {
  resourcesLoaded += increment;
  const progress = (resourcesLoaded / totalResources) * 100;
  loadingBar.style.width = `${progress}%`;
  loadingText.textContent = `Loading Experience... ${Math.floor(progress)}%`;
  
  if (resourcesLoaded >= totalResources) {
    completeLoading();
  }
}

function completeLoading() {
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

function animateEntrance() {
  // Animate the can entrance
  if (drinkModel) {
    drinkModel.position.y = -10;
    gsap.to(drinkModel.position, {
      y: 0,
      duration: 1.5,
      ease: "bounce.out"
    });
  }
}

// Initialize the main scene (hero section)
function initMainScene() {
  console.log('Initializing main scene...');
  mainScene = new THREE.Scene();
  mainScene.background = new THREE.Color(0x050510);
  
  mainCamera = new THREE.PerspectiveCamera(40, drinkModelContainer.clientWidth / drinkModelContainer.clientHeight, 0.1, 1000);
  mainCamera.position.set(0, 2, 8);
  
  mainRenderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
  mainRenderer.setSize(drinkModelContainer.clientWidth, drinkModelContainer.clientHeight);
  mainRenderer.setPixelRatio(window.devicePixelRatio);
  mainRenderer.shadowMap.enabled = true;
  mainRenderer.shadowMap.type = THREE.PCFSoftShadowMap;
  drinkModelContainer.appendChild(mainRenderer.domElement);
  
  // Add lights
  const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
  mainScene.add(ambientLight);
  
  const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
  directionalLight.position.set(5, 10, 7.5);
  directionalLight.castShadow = true;
  directionalLight.shadow.mapSize.width = 1024;
  directionalLight.shadow.mapSize.height = 1024;
  mainScene.add(directionalLight);
  
  // Add a spotlight for dramatic effect
  const spotLight = new THREE.SpotLight(0x00bfff, 1);
  spotLight.position.set(-5, 10, 2);
  spotLight.angle = Math.PI / 6;
  spotLight.penumbra = 0.2;
  spotLight.castShadow = true;
  mainScene.add(spotLight);
  
  // Add a platform for the can
  const platformGeometry = new THREE.CylinderGeometry(3, 3, 0.2, 32);
  const platformMaterial = new THREE.MeshStandardMaterial({ 
    color: 0x333333,
    metalness: 0.8,
    roughness: 0.2
  });
  const platform = new THREE.Mesh(platformGeometry, platformMaterial);
  platform.position.y = -2;
  platform.receiveShadow = true;
  mainScene.add(platform);
  
  // Load environment map
  const envMapLoader = new THREE.CubeTextureLoader();
  const envMap = envMapLoader.load([
    'https://cdn.jsdelivr.net/gh/mrdoob/three.js@master/examples/textures/cube/Park3Med/px.jpg',
    'https://cdn.jsdelivr.net/gh/mrdoob/three.js@master/examples/textures/cube/Park3Med/nx.jpg',
    'https://cdn.jsdelivr.net/gh/mrdoob/three.js@master/examples/textures/cube/Park3Med/py.jpg',
    'https://cdn.jsdelivr.net/gh/mrdoob/three.js@master/examples/textures/cube/Park3Med/ny.jpg',
    'https://cdn.jsdelivr.net/gh/mrdoob/three.js@master/examples/textures/cube/Park3Med/pz.jpg',
    'https://cdn.jsdelivr.net/gh/mrdoob/three.js@master/examples/textures/cube/Park3Med/nz.jpg'
  ]);
  mainScene.environment = envMap;
  updateLoadingProgress();
  
  // Create a realistic can directly
  createRealisticCan();
  
  // Create water droplets
  createWaterDroplets();
  
  // Add controls for the main scene
  mainControls = new OrbitControls(mainCamera, mainRenderer.domElement);
  mainControls.enableDamping = true;
  mainControls.dampingFactor = 0.05;
  mainControls.enableZoom = false;
  mainControls.enablePan = false;
  mainControls.autoRotate = true;
  mainControls.autoRotateSpeed = 1;
}

function createRealisticCan() {
  console.log('Creating a realistic can...');
  
  // Create a group to hold all can parts
  drinkModel = new THREE.Group();
  
  // Create the main can body with a slight taper for realism
  const canHeight = 4;
  const canTopRadius = 1;
  const canBottomRadius = 0.9; // Slightly narrower at the bottom for realism
  
  // Use more segments for smoother curves
  const canGeometry = new THREE.CylinderGeometry(canTopRadius, canBottomRadius, canHeight, 64, 4);
  
  // Create a texture for the can label
  const canvasTexture = createCanLabelTexture();
  
  // Create materials for different parts of the can
  const canTopMaterial = new THREE.MeshPhysicalMaterial({
    color: 0xcccccc,
    metalness: 0.9,
    roughness: 0.1,
    envMapIntensity: 1,
    clearcoat: 0.5,
    clearcoatRoughness: 0.1
  });
  
  const canBodyMaterial = new THREE.MeshPhysicalMaterial({
    map: canvasTexture,
    metalness: 0.7,
    roughness: 0.2,
    envMapIntensity: 0.8,
    clearcoat: 0.3,
    clearcoatRoughness: 0.2
  });
  
  // Create the main body of the can
  const canBody = new THREE.Mesh(canGeometry, canBodyMaterial);
  canBody.castShadow = true;
  canBody.receiveShadow = true;
  drinkModel.add(canBody);
  
  // Create the top rim of the can with a more realistic shape
  const topRimCurvePoints = [];
  for (let i = 0; i <= 10; i++) {
    const t = i / 10;
    // Create a curved profile for the rim
    if (i < 5) {
      // Outer curve
      const x = canTopRadius + 0.1 * Math.sin(t * Math.PI);
      const y = canHeight / 2 + 0.1 * t;
      topRimCurvePoints.push(new THREE.Vector2(x, y));
    } else {
      // Inner curve connecting to the top
      const x = canTopRadius - 0.1 * (t - 0.5) * 2;
      const y = canHeight / 2 + 0.1 - 0.05 * (t - 0.5) * 2;
      topRimCurvePoints.push(new THREE.Vector2(x, y));
    }
  }
  
  const topRimGeometry = new THREE.LatheGeometry(topRimCurvePoints, 64);
  const topRim = new THREE.Mesh(topRimGeometry, canTopMaterial);
  topRim.castShadow = true;
  topRim.receiveShadow = true;
  drinkModel.add(topRim);
  
  // Create the bottom rim of the can with a more realistic curve
  const bottomRimCurvePoints = [];
  for (let i = 0; i <= 10; i++) {
    const t = i / 10;
    // Create a curved profile for the bottom rim
    if (i < 5) {
      // Outer curve
      const x = canBottomRadius + 0.05 * Math.sin(t * Math.PI);
      const y = -canHeight / 2 - 0.05 * t;
      bottomRimCurvePoints.push(new THREE.Vector2(x, y));
    } else {
      // Inner curve connecting to the bottom
      const x = canBottomRadius - 0.05 * (t - 0.5) * 2;
      const y = -canHeight / 2 - 0.05 + 0.025 * (t - 0.5) * 2;
      bottomRimCurvePoints.push(new THREE.Vector2(x, y));
    }
  }
  
  const bottomRimGeometry = new THREE.LatheGeometry(bottomRimCurvePoints, 64);
  const bottomRim = new THREE.Mesh(bottomRimGeometry, canTopMaterial);
  bottomRim.castShadow = true;
  bottomRim.receiveShadow = true;
  drinkModel.add(bottomRim);
  
  // Create the top of the can with a slight dome
  const topCurvePoints = [];
  for (let i = 0; i <= 10; i++) {
    const t = i / 10;
    const x = canTopRadius * (1 - t);
    // Create a slight dome shape
    const y = canHeight / 2 + 0.1 + 0.05 * Math.sin(t * Math.PI);
    topCurvePoints.push(new THREE.Vector2(x, y));
  }
  
  const topGeometry = new THREE.LatheGeometry(topCurvePoints, 64);
  const top = new THREE.Mesh(topGeometry, canTopMaterial);
  top.castShadow = true;
  top.receiveShadow = true;
  drinkModel.add(top);
  
  // Create the pull tab
  createPullTab(drinkModel, canTopRadius, canHeight, canTopMaterial);
  
  // Create the bottom of the can with a realistic concave curve
  const bottomCurvePoints = [];
  for (let i = 0; i <= 10; i++) {
    const t = i / 10;
    const x = canBottomRadius * (1 - t);
    // Create a concave curve for the bottom
    const y = -canHeight / 2 - 0.05 - 0.2 * Math.sin(t * Math.PI / 2);
    bottomCurvePoints.push(new THREE.Vector2(x, y));
  }
  
  const bottomGeometry = new THREE.LatheGeometry(bottomCurvePoints, 64);
  const bottom = new THREE.Mesh(bottomGeometry, canTopMaterial);
  bottom.castShadow = true;
  bottom.receiveShadow = true;
  drinkModel.add(bottom);
  
  // Add the indentation ring near the bottom of the can
  const indentationGeometry = new THREE.TorusGeometry(canBottomRadius + 0.01, 0.03, 16, 64);
  const indentation = new THREE.Mesh(indentationGeometry, canTopMaterial);
  indentation.position.y = -canHeight / 2 + 0.5;
  indentation.rotation.x = Math.PI / 2;
  indentation.castShadow = true;
  indentation.receiveShadow = true;
  drinkModel.add(indentation);
  
  // Add condensation effect (small water droplets on the can surface)
  addCondensation(drinkModel, canTopRadius, canHeight);
  
  // Position the can
  drinkModel.position.y = 0;
  mainScene.add(drinkModel);
  
  // Create a copy for the interactive scene
  interactiveDrinkModel = drinkModel.clone();
  if (interactiveScene) {
    interactiveScene.add(interactiveDrinkModel);
  }
}

function createCanLabelTexture() {
  // Create a canvas for the label texture
  const canvas = document.createElement('canvas');
  canvas.width = 1024;
  canvas.height = 1024;
  const ctx = canvas.getContext('2d');
  
  // Fill background with gradient
  const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
  gradient.addColorStop(0, '#00bfff');
  gradient.addColorStop(1, '#0066ff');
  ctx.fillStyle = gradient;
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  
  // Add brand name
  ctx.font = 'bold 120px Arial';
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  ctx.fillStyle = 'white';
  ctx.fillText('FROSTY', canvas.width / 2, canvas.height / 3);
  
  // Add drink type
  ctx.font = 'bold 80px Arial';
  ctx.fillText('COLA', canvas.width / 2, canvas.height / 2);
  
  // Add some decorative elements
  ctx.beginPath();
  ctx.arc(canvas.width / 2, canvas.height * 0.7, 100, 0, Math.PI * 2);
  ctx.strokeStyle = 'white';
  ctx.lineWidth = 5;
  ctx.stroke();
  
  // Add some bubbles
  for (let i = 0; i < 20; i++) {
    const x = Math.random() * canvas.width;
    const y = Math.random() * canvas.height;
    const radius = 5 + Math.random() * 15;
    
    ctx.beginPath();
    ctx.arc(x, y, radius, 0, Math.PI * 2);
    ctx.fillStyle = 'rgba(255, 255, 255, 0.3)';
    ctx.fill();
  }
  
  // Create texture from canvas
  const texture = new THREE.CanvasTexture(canvas);
  texture.wrapS = THREE.RepeatWrapping;
  texture.repeat.x = 1;
  
  return texture;
}

function createPullTab(parent, canRadius, canHeight, material) {
  // Create a more detailed pull tab
  
  // Create the pull tab base with rounded edges
  const tabBaseShape = new THREE.Shape();
  tabBaseShape.moveTo(-0.25, -0.15);
  tabBaseShape.lineTo(0.25, -0.15);
  tabBaseShape.lineTo(0.25, 0.15);
  tabBaseShape.lineTo(-0.25, 0.15);
  tabBaseShape.lineTo(-0.25, -0.15);
  
  const tabBaseExtrudeSettings = {
    steps: 1,
    depth: 0.05,
    bevelEnabled: true,
    bevelThickness: 0.02,
    bevelSize: 0.02,
    bevelSegments: 3
  };
  
  const tabBaseGeometry = new THREE.ExtrudeGeometry(tabBaseShape, tabBaseExtrudeSettings);
  const tabBase = new THREE.Mesh(tabBaseGeometry, material);
  tabBase.position.set(0, canHeight / 2 + 0.1, 0);
  tabBase.rotation.x = Math.PI / 2;
  tabBase.castShadow = true;
  parent.add(tabBase);
  
  // Create the pull tab ring with a more realistic shape
  const tabRingGeometry = new THREE.TorusGeometry(0.15, 0.03, 16, 32);
  const tabRing = new THREE.Mesh(tabRingGeometry, material);
  tabRing.position.set(0, canHeight / 2 + 0.15, 0.25);
  tabRing.rotation.x = Math.PI / 2;
  tabRing.castShadow = true;
  parent.add(tabRing);
  
  // Create the connection between base and ring with a curved shape
  const tabConnectorShape = new THREE.Shape();
  tabConnectorShape.moveTo(-0.05, 0);
  tabConnectorShape.lineTo(0.05, 0);
  tabConnectorShape.lineTo(0.05, 0.25);
  tabConnectorShape.lineTo(-0.05, 0.25);
  tabConnectorShape.lineTo(-0.05, 0);
  
  const tabConnectorExtrudeSettings = {
    steps: 1,
    depth: 0.05,
    bevelEnabled: true,
    bevelThickness: 0.01,
    bevelSize: 0.01,
    bevelSegments: 3
  };
  
  const tabConnectorGeometry = new THREE.ExtrudeGeometry(tabConnectorShape, tabConnectorExtrudeSettings);
  const tabConnector = new THREE.Mesh(tabConnectorGeometry, material);
  tabConnector.position.set(-0.025, canHeight / 2 + 0.1, 0.025);
  tabConnector.rotation.x = Math.PI / 2;
  tabConnector.castShadow = true;
  parent.add(tabConnector);
  
  // Add a small rivet to connect the tab to the can
  const rivetGeometry = new THREE.CylinderGeometry(0.03, 0.03, 0.02, 16);
  const rivet = new THREE.Mesh(rivetGeometry, material);
  rivet.position.set(0, canHeight / 2 + 0.11, 0);
  rivet.rotation.x = Math.PI / 2;
  rivet.castShadow = true;
  parent.add(rivet);
}

function addCondensation(parent, canRadius, canHeight) {
  // Add small water droplets directly on the can surface
  const condensationGroup = new THREE.Group();
  
  // Create different sizes of droplets
  const smallDroplets = 500; // Tiny droplets (increased for more dense mist)
  const mediumDroplets = 150; // Medium droplets (increased for more coverage)
  const largeDroplets = 50;  // Large droplets with trails (increased for more visible effect)
  
  // Create tiny droplets (mist-like condensation)
  for (let i = 0; i < smallDroplets; i++) {
    const dropSize = 0.003 + Math.random() * 0.01; // Smaller droplets for the mist effect
    const dropletGeometry = new THREE.SphereGeometry(dropSize, 8, 8);
    
    // Create a material that looks like water
    const dropletMaterial = new THREE.MeshPhysicalMaterial({
      color: 0xffffff,
      transparent: true,
      opacity: 0.2 + Math.random() * 0.3, // More transparent for mist effect
      metalness: 0.0,
      roughness: 0.1,
      clearcoat: 1.0,
      clearcoatRoughness: 0.1,
      ior: 1.4, // Index of refraction for water
      transmission: 0.95,
      reflectivity: 0.2
    });
    
    const droplet = new THREE.Mesh(dropletGeometry, dropletMaterial);
    
    // Position the droplet on the can surface - cluster them more on the lower part
    // as condensation tends to form more heavily at the bottom
    const theta = Math.random() * Math.PI * 2;
    const heightBias = Math.pow(Math.random(), 2); // More droplets toward bottom
    const height = (Math.random() * canHeight - canHeight / 2) * (1 - heightBias * 0.5);
    
    // Add slight randomness to the radius to make droplets appear more natural
    const radiusVariation = 0.005 * Math.random();
    droplet.position.x = Math.cos(theta) * (canRadius + radiusVariation);
    droplet.position.z = Math.sin(theta) * (canRadius + radiusVariation);
    droplet.position.y = height;
    
    // Slightly rotate droplet to face outward from can
    droplet.lookAt(droplet.position.x * 2, droplet.position.y, droplet.position.z * 2);
    
    condensationGroup.add(droplet);
  }
  
  // Create medium-sized droplets
  for (let i = 0; i < mediumDroplets; i++) {
    // Create a more realistic water droplet shape
    const dropSize = 0.015 + Math.random() * 0.035;
    
    // Create a custom droplet shape that's flatter against the surface
    // Using a simple half-sphere for better performance
    const segments = 16;
    const halfSphereGeometry = new THREE.SphereGeometry(
      dropSize, 
      segments, 
      segments, 
      0, Math.PI * 2, 
      0, Math.PI / 2
    );
    halfSphereGeometry.scale(1, 0.7, 1); // Flatten it against the can
    
    // Create a material that looks like water
    const dropletMaterial = new THREE.MeshPhysicalMaterial({
      color: 0xffffff,
      transparent: true,
      opacity: 0.6 + Math.random() * 0.3,
      metalness: 0.1,
      roughness: 0.1,
      clearcoat: 1.0,
      clearcoatRoughness: 0.1,
      ior: 1.4, // Index of refraction for water
      transmission: 0.95,
      reflectivity: 0.3
    });
    
    const droplet = new THREE.Mesh(halfSphereGeometry, dropletMaterial);
    
    // Position the droplet on the can surface with bottom bias
    const theta = Math.random() * Math.PI * 2;
    const heightBias = Math.pow(Math.random(), 1.5); // More droplets toward bottom
    const height = (Math.random() * canHeight - canHeight / 2) * (1 - heightBias * 0.7);
    
    // Position and orient the droplet to stick to the can surface
    droplet.position.x = Math.cos(theta) * canRadius;
    droplet.position.z = Math.sin(theta) * canRadius;
    droplet.position.y = height;
    
    // Rotate the droplet to align with the can surface
    droplet.lookAt(0, droplet.position.y, 0);
    // Adjust rotation to make it stick to the surface properly
    droplet.rotateX(-Math.PI / 2);
    
    condensationGroup.add(droplet);
  }
  
  // Create large droplets with trails (like droplets sliding down)
  for (let i = 0; i < largeDroplets; i++) {
    const dropSize = 0.03 + Math.random() * 0.05;
    
    // Create the main droplet with a teardrop shape
    const shape = new THREE.Shape();
    const width = dropSize;
    const height = dropSize * (1.2 + Math.random() * 0.5);
    
    // Draw a teardrop shape
    shape.moveTo(0, height);
    shape.bezierCurveTo(
      width * 0.5, height * 0.75,
      width, height * 0.25,
      0, -height
    );
    shape.bezierCurveTo(
      -width, height * 0.25,
      -width * 0.5, height * 0.75,
      0, height
    );
    
    const extrudeSettings = {
      steps: 1,
      depth: dropSize * 0.8,
      bevelEnabled: true,
      bevelThickness: dropSize * 0.2,
      bevelSize: dropSize * 0.2,
      bevelSegments: 8
    };
    
    const dropletGeometry = new THREE.ExtrudeGeometry(shape, extrudeSettings);
    
    const dropletMaterial = new THREE.MeshPhysicalMaterial({
      color: 0xffffff,
      transparent: true,
      opacity: 0.8,
      metalness: 0.1,
      roughness: 0.05,
      clearcoat: 1.0,
      clearcoatRoughness: 0.1,
      ior: 1.4,
      transmission: 0.95,
      reflectivity: 0.4
    });
    
    const mainDroplet = new THREE.Mesh(dropletGeometry, dropletMaterial);
    mainDroplet.scale.set(0.5, 0.5, 0.5); // Scale down the extruded geometry
    
    // Position the droplet with strong bottom bias
    const theta = Math.random() * Math.PI * 2;
    const heightBias = Math.pow(Math.random(), 2); // Heavy bottom bias for larger droplets
    const heightPos = (Math.random() * canHeight * 0.8 - canHeight * 0.4) * (1 - heightBias * 0.8);
    
    mainDroplet.position.x = Math.cos(theta) * (canRadius + 0.01);
    mainDroplet.position.z = Math.sin(theta) * (canRadius + 0.01);
    mainDroplet.position.y = heightPos;
    
    // Create a group for this droplet and its trail
    const dropletWithTrail = new THREE.Group();
    dropletWithTrail.add(mainDroplet);
    
    // Add a trail below the droplet (like it's sliding down)
    if (Math.random() > 0.2) { // Most large droplets have trails
      const trailLength = 0.1 + Math.random() * 0.4; // Longer trails for more realism
      
      // Create a trail using a spline curve for more natural shape
      const curve = new THREE.CurvePath();
      
      // Create points for the curve
      const start = new THREE.Vector3(0, 0, 0);
      const end = new THREE.Vector3(0, -trailLength, 0);
      
      // Add some randomness to the curve
      const midPoint1 = new THREE.Vector3(
        (Math.random() - 0.5) * 0.02,
        -trailLength * 0.33,
        (Math.random() - 0.5) * 0.02
      );
      
      const midPoint2 = new THREE.Vector3(
        (Math.random() - 0.5) * 0.02,
        -trailLength * 0.66,
        (Math.random() - 0.5) * 0.02
      );
      
      // Create a spline with these points
      const spline = new THREE.CubicBezierCurve3(start, midPoint1, midPoint2, end);
      curve.add(spline);
      
      // Create a tube geometry along the curve
      const tubeGeometry = new THREE.TubeGeometry(
        spline,
        10,              // tubularSegments
        dropSize * 0.2,  // radius
        8,               // radialSegments
        false            // closed
      );
      
      const trailMaterial = new THREE.MeshPhysicalMaterial({
        color: 0xffffff,
        transparent: true,
        opacity: 0.4 + Math.random() * 0.2,
        metalness: 0.0,
        roughness: 0.2,
        clearcoat: 0.5,
        transmission: 0.95,
        reflectivity: 0.2
      });
      
      const trail = new THREE.Mesh(tubeGeometry, trailMaterial);
      trail.position.y = -dropSize * 0.3;
      mainDroplet.add(trail);
      
      // Add a few small droplets along the trail
      const smallDropCount = Math.floor(Math.random() * 4) + 1;
      for (let j = 0; j < smallDropCount; j++) {
        const smallDropSize = dropSize * (0.15 + Math.random() * 0.15);
        const smallDropGeometry = new THREE.SphereGeometry(smallDropSize, 8, 8);
        const smallDropMaterial = new THREE.MeshPhysicalMaterial({
          color: 0xffffff,
          transparent: true,
          opacity: 0.4 - j * 0.05,
          metalness: 0.0,
          roughness: 0.1,
          clearcoat: 1.0,
          transmission: 0.95,
          reflectivity: 0.2
        });
        
        const smallDrop = new THREE.Mesh(smallDropGeometry, smallDropMaterial);
        
        // Position along the trail
        const trailProgress = (j + 1) / (smallDropCount + 1);
        const trailPos = curve.getPoint(trailProgress);
        smallDrop.position.copy(trailPos);
        
        // Add slight random offset
        smallDrop.position.x += (Math.random() - 0.5) * 0.01;
        smallDrop.position.z += (Math.random() - 0.5) * 0.01;
        
        mainDroplet.add(smallDrop);
      }
    }
    
    // Rotate to align with the can surface
    dropletWithTrail.lookAt(0, dropletWithTrail.position.y, 0);
    dropletWithTrail.rotateX(Math.PI / 2);
    
    // Add a slight random rotation for variety
    dropletWithTrail.rotateZ(Math.random() * Math.PI * 2);
    
    condensationGroup.add(dropletWithTrail);
    
    // Add animation data for some droplets
    if (Math.random() > 0.7) {
      dropletWithTrail.userData = {
        isAnimated: true,
        initialY: dropletWithTrail.position.y,
        speed: 0.0005 + Math.random() * 0.001,
        theta: theta,
        timeOffset: Math.random() * Math.PI * 2
      };
    }
  }
  
  // Create frost effect at the top and bottom edges
  createFrostEffect(condensationGroup, canRadius, canHeight);
  
  parent.add(condensationGroup);
  
  // Return the condensation group for animation
  return condensationGroup;
}

function createFrostEffect(parent, canRadius, canHeight) {
  // Add a frost/mist effect near the top and bottom of the can
  
  // Top frost ring
  const topFrostGeometry = new THREE.TorusGeometry(canRadius, 0.05, 16, 32);
  const frostMaterial = new THREE.MeshPhysicalMaterial({
    color: 0xffffff,
    transparent: true,
    opacity: 0.3,
    metalness: 0.0,
    roughness: 0.5,
    clearcoat: 0.2,
    transmission: 0.6
  });
  
  const topFrost = new THREE.Mesh(topFrostGeometry, frostMaterial);
  topFrost.position.y = canHeight / 2 - 0.1;
  topFrost.rotation.x = Math.PI / 2;
  parent.add(topFrost);
  
  // Bottom frost ring (heavier)
  const bottomFrostGeometry = new THREE.TorusGeometry(canRadius, 0.08, 16, 32);
  const bottomFrostMaterial = new THREE.MeshPhysicalMaterial({
    color: 0xffffff,
    transparent: true,
    opacity: 0.4,
    metalness: 0.0,
    roughness: 0.6,
    clearcoat: 0.2,
    transmission: 0.5
  });
  
  const bottomFrost = new THREE.Mesh(bottomFrostGeometry, bottomFrostMaterial);
  bottomFrost.position.y = -canHeight / 2 + 0.1;
  bottomFrost.rotation.x = Math.PI / 2;
  parent.add(bottomFrost);
  
  // Add random frost patches
  const patchCount = 30;
  for (let i = 0; i < patchCount; i++) {
    const patchSize = 0.1 + Math.random() * 0.2;
    
    // Create a custom frost patch shape
    const patchShape = new THREE.Shape();
    const segments = 8;
    
    // Create an irregular shape for the frost patch
    for (let j = 0; j <= segments; j++) {
      const angle = (j / segments) * Math.PI * 2;
      const radius = patchSize * (0.7 + Math.random() * 0.6);
      const x = Math.cos(angle) * radius;
      const y = Math.sin(angle) * radius;
      
      if (j === 0) {
        patchShape.moveTo(x, y);
      } else {
        patchShape.lineTo(x, y);
      }
    }
    
    const patchGeometry = new THREE.ShapeGeometry(patchShape);
    const patchMaterial = new THREE.MeshPhysicalMaterial({
      color: 0xffffff,
      transparent: true,
      opacity: 0.1 + Math.random() * 0.2,
      metalness: 0.0,
      roughness: 0.7,
      clearcoat: 0.1,
      transmission: 0.3
    });
    
    const patch = new THREE.Mesh(patchGeometry, patchMaterial);
    
    // Position mostly at the bottom half of the can
    const theta = Math.random() * Math.PI * 2;
    const heightBias = Math.pow(Math.random(), 3); // Very heavy bottom bias for frost
    const height = (Math.random() * canHeight - canHeight / 2) * (1 - heightBias * 0.9);
    
    patch.position.x = Math.cos(theta) * canRadius;
    patch.position.z = Math.sin(theta) * canRadius;
    patch.position.y = height;
    
    // Rotate to align with the can surface
    patch.lookAt(0, patch.position.y, 0);
    patch.rotateX(Math.PI / 2);
    
    // Add slight random rotation for variety
    patch.rotateZ(Math.random() * Math.PI * 2);
    
    parent.add(patch);
  }
}

function createWaterDroplets() {
  // Empty function - we'll use condensation on the can instead of floating bubbles
  updateLoadingProgress();
}

// Initialize the interactive scene (experience section)
function initInteractiveScene() {
  console.log('Initializing interactive scene...');
  interactiveScene = new THREE.Scene();
  interactiveScene.background = new THREE.Color(0x050510);
  
  interactiveCamera = new THREE.PerspectiveCamera(50, interactiveDrinkContainer.clientWidth / interactiveDrinkContainer.clientHeight, 0.1, 1000);
  interactiveCamera.position.set(0, 0, 5);
  
  interactiveRenderer = new THREE.WebGLRenderer({ antialias: true });
  interactiveRenderer.setSize(interactiveDrinkContainer.clientWidth, interactiveDrinkContainer.clientHeight);
  interactiveRenderer.setPixelRatio(window.devicePixelRatio);
  interactiveRenderer.shadowMap.enabled = true;
  interactiveRenderer.shadowMap.type = THREE.PCFSoftShadowMap;
  
  if (interactiveDrinkContainer) {
    interactiveDrinkContainer.appendChild(interactiveRenderer.domElement);
    
    // Add controls for the interactive scene
    interactiveControls = new OrbitControls(interactiveCamera, interactiveRenderer.domElement);
    interactiveControls.enableDamping = true;
    interactiveControls.dampingFactor = 0.05;
    
    // Add lights
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
    interactiveScene.add(ambientLight);
    
    const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
    directionalLight.position.set(5, 10, 7.5);
    directionalLight.castShadow = true;
    interactiveScene.add(directionalLight);
  } else {
    console.warn('Interactive drink container not found in the DOM');
  }
}

// Animation loop
function animate() {
  requestAnimationFrame(animate);
  
  const elapsedTime = clock.getElapsedTime();
  
  // Skip animating water droplets since we're not using them anymore
  
  // Animate the can
  if (drinkModel) {
    drinkModel.rotation.y += 0.005;
    
    // Animate condensation droplets if they exist
    if (drinkModel.children) {
      drinkModel.children.forEach(child => {
        if (child.type === "Group" && child.children) {
          child.children.forEach(droplet => {
            if (droplet.userData && droplet.userData.isAnimated) {
              // Slowly move droplets down
              droplet.position.y -= droplet.userData.speed;
              
              // Add subtle wobble
              const wobble = Math.sin(elapsedTime * 0.5 + droplet.userData.timeOffset) * 0.0005;
              droplet.position.x += wobble * Math.cos(droplet.userData.theta);
              droplet.position.z += wobble * Math.sin(droplet.userData.theta);
              
              // Reset position if it goes too far down
              if (droplet.position.y < droplet.userData.initialY - 0.5) {
                droplet.position.y = droplet.userData.initialY;
              }
            }
          });
        }
      });
    }
  }
  
  if (interactiveDrinkModel) {
    interactiveDrinkModel.rotation.y += 0.002;
  }
  
  // Update controls
  if (mainControls) mainControls.update();
  if (interactiveControls) interactiveControls.update();
  
  // Render scenes
  if (mainRenderer && mainScene && mainCamera) {
    mainRenderer.render(mainScene, mainCamera);
  }
  
  if (interactiveRenderer && interactiveScene && interactiveCamera) {
    interactiveRenderer.render(interactiveScene, interactiveCamera);
  }
}

// Handle window resize
function onWindowResize() {
  if (mainCamera && mainRenderer && drinkModelContainer) {
    mainCamera.aspect = drinkModelContainer.clientWidth / drinkModelContainer.clientHeight;
    mainCamera.updateProjectionMatrix();
    mainRenderer.setSize(drinkModelContainer.clientWidth, drinkModelContainer.clientHeight);
  }
  
  if (interactiveCamera && interactiveRenderer && interactiveDrinkContainer) {
    interactiveCamera.aspect = interactiveDrinkContainer.clientWidth / interactiveDrinkContainer.clientHeight;
    interactiveCamera.updateProjectionMatrix();
    interactiveRenderer.setSize(interactiveDrinkContainer.clientWidth, interactiveDrinkContainer.clientHeight);
  }
}

// Initialize everything
function init() {
  console.log('Initializing application...');
  
  // Add event listener for the skip button
  if (skipLoadingButton) {
    skipLoadingButton.addEventListener('click', () => {
      console.log('Skip button clicked');
      resourcesLoaded = totalResources;
      completeLoading();
    });
  } else {
    console.warn('Skip loading button not found in the DOM');
  }
  
  // Initialize scenes
  initMainScene();
  initInteractiveScene();
  
  // Start animation loop
  animate();
  
  // Add window resize listener
  window.addEventListener('resize', onWindowResize);
  
  // Add timeout to prevent infinite loading
  setTimeout(() => {
    if (resourcesLoaded < totalResources) {
      console.warn('Loading is taking too long, completing loading process.');
      completeLoading();
    }
  }, 10000); // 10 seconds timeout
}

document.addEventListener('DOMContentLoaded', init);