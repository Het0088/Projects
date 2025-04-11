import random
from database.db import get_session
from database.models import Product, Location, Inventory, Activity
import datetime

def create_test_locations():
    """Create store and warehouse locations for testing"""
    locations = [
        # Stores
        {"name": "Main Store", "type": "STORE", "address": "123 Main Street, City Center"},
        {"name": "Downtown Branch", "type": "STORE", "address": "45 Market Square, Downtown"},
        {"name": "North Mall Store", "type": "STORE", "address": "North Shopping Mall, 3rd Floor"},
        
        # Warehouses
        {"name": "Central Warehouse", "type": "WAREHOUSE", "address": "Industrial Zone, Building 7A"},
        {"name": "East Distribution Center", "type": "WAREHOUSE", "address": "456 East Highway, Outskirts"}
    ]
    
    location_ids = {}
    
    with get_session() as session:
        # Check if locations already exist
        existing = session.query(Location).count()
        if existing > 0:
            print(f"Locations already exist, skipping creation. Found {existing} locations.")
            
            # Get existing location IDs for reference
            all_locations = session.query(Location).all()
            for loc in all_locations:
                location_ids[loc.name] = loc.id
            
            return location_ids
        
        # Create the locations
        for loc_data in locations:
            location = Location(**loc_data, is_active=True)
            session.add(location)
            session.flush()  # Flush to get the ID
            location_ids[location.name] = location.id
        
        print(f"Created {len(locations)} test locations")
    
    return location_ids

def create_test_products(num_products=50, force=False):
    """Create test products with random data"""
    # Product categories
    categories = ["Clothing", "Electronics", "Home & Kitchen", "Sports", "Beauty", "Books", "Toys", "Office"]
    
    # Product name templates per category
    product_templates = {
        "Clothing": [
            "Men's {color} T-Shirt {size}",
            "Women's {color} Blouse {size}",
            "Kids {color} Hoodie {size}",
            "Unisex {color} Jeans {size}",
            "Cotton {color} Socks",
            "{color} Baseball Cap",
            "Winter {color} Jacket {size}",
            "Summer {color} Shorts {size}"
        ],
        "Electronics": [
            "{brand} Smartphone Model {model}",
            "{brand} Laptop {size}\"",
            "{brand} Wireless Headphones",
            "{brand} Bluetooth Speaker",
            "{brand} Smart Watch",
            "{brand} Tablet {size}\"",
            "{brand} Power Bank {capacity}mAh",
            "{brand} Wireless Charger"
        ],
        "Home & Kitchen": [
            "{material} Cutting Board",
            "{color} Kitchen Knife Set",
            "{material} {color} Dinnerware Set",
            "{color} Coffee Maker",
            "{material} {color} Frying Pan",
            "{material} Food Storage Containers",
            "{color} Microwave Oven",
            "{color} Blender"
        ],
        "Sports": [
            "{brand} Running Shoes {size}",
            "{brand} Yoga Mat",
            "{brand} Water Bottle",
            "{brand} Fitness Tracker",
            "{color} Tennis Racket",
            "{color} Basketball",
            "{brand} {color} Gym Bag",
            "{color} Resistance Bands Set"
        ],
        "Beauty": [
            "{brand} Facial Cleanser",
            "{brand} Moisturizer",
            "{brand} Lipstick {color}",
            "{brand} Shampoo",
            "{brand} Conditioner",
            "{brand} {color} Nail Polish",
            "{brand} Mascara",
            "{brand} Perfume"
        ],
        "Books": [
            "Cookbook: {title}",
            "Fiction: {title}",
            "Self-Help: {title}",
            "Biography: {title}",
            "History: {title}",
            "Science: {title}",
            "Business: {title}",
            "Art: {title}"
        ],
        "Toys": [
            "{color} Building Blocks",
            "{brand} Action Figure",
            "{color} Stuffed Animal",
            "{brand} Board Game",
            "{brand} RC Car",
            "{color} Puzzle {size} pieces",
            "{brand} Doll",
            "{color} Play-Doh Set"
        ],
        "Office": [
            "{color} Notebook Set",
            "{color} Desk Organizer",
            "{brand} Stapler",
            "{color} File Folders",
            "{brand} Printer Paper",
            "{color} Sticky Notes",
            "{brand} Pen Set",
            "{color} Desk Lamp"
        ]
    }
    
    # Properties to fill in templates
    colors = ["Red", "Blue", "Green", "Black", "White", "Purple", "Yellow", "Orange", "Grey", "Brown", "Pink"]
    sizes = ["S", "M", "L", "XL", "XXL", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "32", "34", "36", "38", "40", "42"]
    brands = ["TechX", "Gadgetron", "FitLife", "HomeStyle", "StyleHub", "EcoGoods", "ProActive", "LuxuryPlus", "ValueZone", "NextGen"]
    materials = ["Wooden", "Stainless Steel", "Ceramic", "Glass", "Plastic", "Silicone", "Bamboo", "Marble", "Cast Iron"]
    models = ["X1", "Pro", "Elite", "Ultra", "Lite", "Plus", "Max", "Mini", "Standard", "Premium"]
    capacities = ["5000", "10000", "15000", "20000", "25000", "30000"]
    book_titles = [
        "The Art of Cooking", "Mystery of the Mountains", "Success Habits", "Great Leaders", 
        "Ancient Civilizations", "The Universe Explained", "Startup Strategy", "Creative Expressions"
    ]
    
    products_created = 0
    
    with get_session() as session:
        # Check if products already exist and skip if they do
        existing = session.query(Product).count()
        if existing > 0 and not force:
            print(f"Products already exist, skipping creation. Found {existing} products.")
            return
        
        for _ in range(num_products):
            # Select random category and template
            category = random.choice(categories)
            template = random.choice(product_templates[category])
            
            # Fill in template with random properties
            name = template.format(
                color=random.choice(colors) if "{color}" in template else "",
                size=random.choice(sizes) if "{size}" in template else "",
                brand=random.choice(brands) if "{brand}" in template else "",
                material=random.choice(materials) if "{material}" in template else "",
                model=random.choice(models) if "{model}" in template else "",
                capacity=random.choice(capacities) if "{capacity}" in template else "",
                title=random.choice(book_titles) if "{title}" in template else ""
            )
            
            # Create SKU from first letters of name + random number
            sku_base = ''.join([word[0] for word in name.split() if word[0].isalpha()])
            sku = f"{sku_base.upper()}{random.randint(1000, 9999)}"
            
            # Set price and cost based on category
            if category == "Electronics":
                price = round(random.uniform(100, 1000), 2)
                cost_price = round(price * 0.6, 2)  # 40% margin
            elif category in ["Home & Kitchen", "Sports"]:
                price = round(random.uniform(20, 200), 2)
                cost_price = round(price * 0.7, 2)  # 30% margin
            else:
                price = round(random.uniform(10, 100), 2)
                cost_price = round(price * 0.5, 2)  # 50% margin
            
            # Create product description
            description = f"High-quality {category.lower()} product. {name} is perfect for everyday use."
            
            # Create product object
            product = Product(
                sku=sku,
                name=name,
                description=description,
                category=category,
                price=price,
                cost_price=cost_price,
                tax_rate=0.08,  # 8% tax
                is_active=True
            )
            
            session.add(product)
            products_created += 1
        
        # Commit all products at once
        session.commit()
        print(f"Created {products_created} test products")

def add_inventory_to_locations(min_qty=5, max_qty=100, force=False):
    """Add inventory for products across locations with random quantities"""
    with get_session() as session:
        # Get all products and locations
        products = session.query(Product).all()
        locations = session.query(Location).all()
        
        if not products or not locations:
            print("No products or locations found. Please run create_test_products() and create_test_locations() first.")
            return
        
        # Check if inventory already exists
        existing = session.query(Inventory).count()
        if existing > 0 and not force:
            print(f"Inventory records already exist, skipping creation. Found {existing} inventory records.")
            return
        
        # Get products that don't have inventory records yet
        products_with_inventory = session.query(Product.id).join(
            Inventory, Product.id == Inventory.product_id
        ).distinct().all()
        
        product_ids_with_inventory = [p[0] for p in products_with_inventory]
        products_without_inventory = [p for p in products if p.id not in product_ids_with_inventory]
        
        if not products_without_inventory and not force:
            print("All products already have inventory. Use force=True to add more.")
            return
            
        target_products = products_without_inventory if not force else products
        
        inventory_created = 0
        
        # For each product, add inventory to 1-3 random locations
        for product in target_products:
            # Select 1-3 random locations for this product
            num_locations = random.randint(1, min(3, len(locations)))
            selected_locations = random.sample(locations, num_locations)
            
            for location in selected_locations:
                # If not using force, check if this product-location combination already exists
                if not force:
                    existing_record = session.query(Inventory).filter(
                        Inventory.product_id == product.id,
                        Inventory.location_id == location.id
                    ).first()
                    
                    if existing_record:
                        continue
                        
                # Set different stock levels based on location type
                if location.type == "WAREHOUSE":
                    quantity = random.randint(max_qty // 2, max_qty)
                    min_stock = max(5, quantity // 10)
                else:  # STORE
                    quantity = random.randint(min_qty, max_qty // 2)
                    min_stock = max(3, quantity // 5)
                
                # Create inventory record
                inventory = Inventory(
                    product_id=product.id,
                    location_id=location.id,
                    quantity=quantity,
                    min_stock_level=min_stock,
                    reorder_quantity=min_stock * 2
                )
                
                session.add(inventory)
                inventory_created += 1
        
        # Log activity
        activity = Activity(
            activity_type="SEED_DATA",
            details=f"Added {inventory_created} inventory records across {len(locations)} locations",
            user_id=1,  # Admin user
            user_name="admin"
        )
        session.add(activity)
        
        # Commit all at once
        session.commit()
        print(f"Created {inventory_created} inventory records")

def seed_all_test_data(force=False):
    """Run all seed data functions in the correct order"""
    print("Starting to seed test data...")
    create_test_locations()
    create_test_products(force=force)
    add_inventory_to_locations(force=force)
    print("Test data seeding completed!")

if __name__ == "__main__":
    seed_all_test_data() 