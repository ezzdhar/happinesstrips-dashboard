// Font Awesome 6 Free Icons List
// This list contains all free icons available in Font Awesome 6
const fontAwesomeIcons = {
    solid: [
        // Popular & Common
        'house', 'home', 'user', 'users', 'heart', 'star', 'check', 'xmark', 'circle', 'square',
        'triangle-exclamation', 'circle-info', 'circle-check', 'circle-xmark', 'circle-question',
        
        // Arrows & Directions
        'arrow-right', 'arrow-left', 'arrow-up', 'arrow-down', 'angles-right', 'angles-left',
        'chevron-right', 'chevron-left', 'chevron-up', 'chevron-down', 'caret-right', 'caret-left',
        
        // Home & Living
        'bed', 'couch', 'bath', 'shower', 'toilet', 'door-open', 'door-closed', 'sink',
        'kitchen-set', 'chair', 'table', 'lamp', 'lightbulb', 'plug', 'fire-burner',
        
        // Kitchen & Dining
        'utensils', 'fork-knife', 'plate-wheat', 'bowl-food', 'bowl-rice', 'mug-hot',
        'coffee', 'wine-glass', 'wine-bottle', 'champagne-glasses', 'martini-glass',
        'blender', 'ice-cream', 'pizza-slice', 'burger', 'hotdog', 'cookie', 'cake-candles',
        
        // Technology & Electronics
        'wifi', 'tv', 'laptop', 'desktop', 'mobile', 'mobile-screen', 'tablet',
        'computer', 'keyboard', 'mouse', 'headphones', 'microphone', 'camera',
        'video', 'gamepad', 'music', 'volume-high', 'volume-low', 'volume-xmark',
        
        // Comfort & Climate
        'snowflake', 'fan', 'fire', 'fire-flame-curved', 'temperature-high', 'temperature-low',
        'temperature-half', 'sun', 'moon', 'cloud', 'wind', 'water',
        
        // Security & Safety
        'lock', 'unlock', 'key', 'shield', 'shield-halved', 'user-shield',
        'bell', 'bell-slash', 'eye', 'eye-slash', 'fingerprint',
        
        // Outdoor & Recreation
        'swimming-pool', 'umbrella-beach', 'tree', 'mountain', 'tent', 'campground',
        'dumbbell', 'person-running', 'person-swimming', 'person-biking', 'person-hiking',
        'spa', 'hot-tub-person', 'volleyball', 'football', 'basketball', 'baseball',
        
        // Transportation & Parking
        'car', 'car-side', 'taxi', 'bus', 'truck', 'van-shuttle', 'motorcycle',
        'bicycle', 'parking', 'square-parking', 'road', 'traffic-light',
        
        // Accessibility & Services
        'elevator', 'wheelchair', 'wheelchair-move', 'person-walking-with-cane',
        'universal-access', 'baby', 'baby-carriage', 'child', 'children',
        
        // Animals & Pets
        'paw', 'dog', 'cat', 'fish', 'horse', 'dove', 'dragon', 'hippo',
        
        // Cleaning & Laundry
        'broom', 'soap', 'pump-soap', 'spray-can', 'sponge', 'shirt',
        
        // Shopping & Commerce
        'cart-shopping', 'bag-shopping', 'basket-shopping', 'store', 'shop',
        'credit-card', 'money-bill', 'coins', 'wallet', 'receipt', 'barcode',
        'tag', 'tags', 'gift', 'percent', 'dollar-sign', 'sterling-sign',
        
        // Food & Drinks
        'apple-whole', 'carrot', 'lemon', 'pepper-hot', 'bread-slice', 'cheese',
        'egg', 'drumstick-bite', 'fish-fins', 'shrimp', 'bacon', 'candy-cane',
        
        // Medical & Health
        'heart-pulse', 'stethoscope', 'syringe', 'pills', 'capsules', 'prescription-bottle',
        'thermometer', 'band-aid', 'briefcase-medical', 'hospital', 'user-doctor',
        'tooth', 'smoking', 'smoking-ban', 'lungs', 'brain',
        
        // Communication
        'phone', 'mobile-screen-button', 'envelope', 'envelope-open', 'comment', 'comments',
        'message', 'inbox', 'paper-plane', 'at', 'hashtag', 'share-nodes',
        
        // Office & Business
        'briefcase', 'building', 'building-columns', 'city', 'landmark',
        'file', 'folder', 'folder-open', 'file-pdf', 'file-word', 'file-excel',
        'clipboard', 'pen', 'pencil', 'marker', 'highlighter', 'eraser',
        'scissors', 'paperclip', 'thumbtack', 'calendar', 'calendar-days',
        
        // Education & Learning
        'book', 'book-open', 'graduation-cap', 'school', 'chalkboard',
        'chalkboard-user', 'user-graduate', 'spell-check', 'language',
        
        // Media & Entertainment
        'image', 'images', 'photo-film', 'film', 'clapperboard', 'masks-theater',
        'play', 'pause', 'stop', 'forward', 'backward', 'circle-play',
        
        // Weather & Nature
        'cloud-sun', 'cloud-moon', 'cloud-rain', 'cloud-bolt', 'snowflake',
        'rainbow', 'meteor', 'seedling', 'leaf', 'clover', 'flower-tulip',
        
        // Tools & Construction
        'hammer', 'wrench', 'screwdriver', 'screwdriver-wrench', 'toolbox',
        'gears', 'gear', 'bolt', 'nut', 'ruler', 'ruler-combined',
        
        // Navigation & Maps
        'location-dot', 'map', 'map-location', 'map-location-dot', 'compass',
        'route', 'signs-post', 'diamond-turn-right', 'globe',
        
        // Time & Clock
        'clock', 'stopwatch', 'hourglass', 'hourglass-half', 'timer',
        
        // Social & People
        'user-group', 'people-group', 'person', 'child-reaching', 'face-smile',
        'face-frown', 'face-meh', 'handshake', 'hands-clapping', 'thumbs-up', 'thumbs-down',
        
        // Symbols & Signs
        'plus', 'minus', 'equals', 'divide', 'xmark', 'check',
        'star', 'crown', 'gem', 'award', 'trophy', 'medal',
        'ribbon', 'certificate', 'bookmark', 'flag', 'ban',
        
        // UI & Controls
        'bars', 'ellipsis', 'ellipsis-vertical', 'sliders', 'filter',
        'magnifying-glass', 'magnifying-glass-plus', 'magnifying-glass-minus',
        'rotate', 'rotate-right', 'arrows-rotate', 'download', 'upload',
        'share', 'print', 'floppy-disk', 'trash', 'trash-can',
        
        // Miscellaneous
        'battery-full', 'battery-half', 'battery-empty', 'plug-circle-bolt',
        'lightbulb', 'fire-extinguisher', 'box', 'boxes-stacked', 'cube', 'cubes',
        'puzzle-piece', 'magnet', 'anchor', 'parachute-box', 'rocket', 'satellite'
    ],
    
    regular: [
        'heart', 'star', 'user', 'circle', 'square', 'face-smile', 'face-frown',
        'face-meh', 'thumbs-up', 'thumbs-down', 'comment', 'comments', 'envelope',
        'file', 'folder', 'folder-open', 'calendar', 'calendar-days', 'clock',
        'bookmark', 'image', 'images', 'eye', 'eye-slash', 'hand', 'handshake',
        'bell', 'bell-slash', 'circle-check', 'circle-xmark', 'circle-question',
        'clipboard', 'copy', 'floppy-disk', 'lightbulb', 'moon', 'sun',
        'credit-card', 'keyboard', 'flag', 'map', 'paper-plane', 'star-half'
    ],
    
    brands: [
        'facebook', 'twitter', 'instagram', 'youtube', 'linkedin', 'whatsapp',
        'telegram', 'snapchat', 'tiktok', 'pinterest', 'reddit', 'discord',
        'google', 'apple', 'microsoft', 'amazon', 'paypal', 'stripe',
        'github', 'gitlab', 'bitbucket', 'stack-overflow', 'npm', 'yarn',
        'react', 'angular', 'vuejs', 'node-js', 'python', 'java', 'php',
        'wordpress', 'drupal', 'joomla', 'shopify', 'wix', 'squarespace'
    ]
};

// Generate full icon list with classes
function getAllFontAwesomeIcons() {
    const icons = [];
    
    // Add solid icons
    fontAwesomeIcons.solid.forEach(icon => {
        icons.push({
            id: `fas fa-${icon}`,
            name: icon.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
            category: 'Solid',
            search: icon
        });
    });
    
    // Add regular icons
    fontAwesomeIcons.regular.forEach(icon => {
        icons.push({
            id: `far fa-${icon}`,
            name: icon.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
            category: 'Regular',
            search: icon
        });
    });
    
    // Add brand icons
    fontAwesomeIcons.brands.forEach(icon => {
        icons.push({
            id: `fab fa-${icon}`,
            name: icon.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
            category: 'Brands',
            search: icon
        });
    });
    
    return icons;
}

// Make it available globally
window.getAllFontAwesomeIcons = getAllFontAwesomeIcons;
