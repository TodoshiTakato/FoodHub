# 🍕 FoodHub API Documentation

**Multi-channel SaaS platform for restaurants** - Complete API documentation for developers

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-blue.svg)](https://php.net)
[![API](https://img.shields.io/badge/API-REST-green.svg)](http://localhost/api/documentation)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

---

## 📋 Table of Contents

- [🚀 Quick Start](#-quick-start)
- [🔐 Authentication](#-authentication)
- [🏪 Restaurants](#-restaurants)
- [📋 Menus](#-menus)
- [🍕 Products](#-products)
- [📦 Orders](#-orders)
- [🌐 Multilingual Support](#-multilingual-support)
- [📊 Channel Support](#-channel-support)
- [🔧 Setup & Installation](#-setup--installation)

---

## 🚀 Quick Start

### Base URL
```
http://localhost/api/v1
```

### Interactive Documentation
Access the complete Swagger UI documentation at:
```
http://localhost/api/documentation
```

### Authentication
All protected endpoints require Bearer token authentication:
```bash
Authorization: Bearer your-token-here
```

---

## 🔐 Authentication

### Register User
**POST** `/api/v1/auth/register`

Create a new user account.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+998901234567",
  "restaurant_id": 1
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+998901234567"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
    "token_type": "Bearer"
  },
  "message": "User registered successfully"
}
```

### Login
**POST** `/api/v1/auth/login`

Authenticate user and get access token.

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

### User Profile
**GET** `/api/v1/auth/me` 🔒

Get authenticated user profile information.

### Logout
**POST** `/api/v1/auth/logout` 🔒

Revoke current access token.

---

## 🏪 Restaurants

### List Restaurants
**GET** `/api/v1/restaurants`

Get paginated list of all active restaurants.

**Query Parameters:**
- `page` - Page number (default: 1)

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": "Pizza Palace",
        "slug": "pizza-palace",
        "description": "Best pizza in town",
        "address": {
          "street": "Main Street 123",
          "city": "Tashkent",
          "country": "Uzbekistan"
        },
        "phone": "+998901234567",
        "currency": "USD"
      }
    ]
  },
  "message": "Restaurants retrieved successfully"
}
```

### Restaurant Details
**GET** `/api/v1/restaurants/{id}`

Get detailed information about specific restaurant.

### Create Restaurant
**POST** `/api/v1/restaurants` 🔒

Create a new restaurant (requires authentication).

**Request Body:**
```json
{
  "name": "Pizza Palace",
  "description": "Best pizza in town",
  "phone": "+998901234567",
  "email": "info@pizzapalace.uz",
  "address": {
    "street": "Main Street 123",
    "city": "Tashkent",
    "country": "Uzbekistan"
  },
  "latitude": 41.2995,
  "longitude": 69.2401,
  "currency": "USD",
  "languages": ["en", "ru", "uz"],
  "business_hours": {
    "monday": {"open": "09:00", "close": "22:00"},
    "tuesday": {"open": "09:00", "close": "22:00"}
  }
}
```

### Restaurant Menus
**GET** `/api/v1/restaurants/{restaurant_slug}/menus`

Get all menus for specific restaurant with products.

### Restaurant Products
**GET** `/api/v1/restaurants/{restaurant_slug}/products`

Get all products for specific restaurant.

---

## 📋 Menus

### List Menus
**GET** `/api/v1/menus`

Get paginated list of menus with filtering options.

**Query Parameters:**
- `restaurant_id` - Filter by restaurant ID
- `channel` - Filter by channel (`web`, `mobile`, `telegram`, `whatsapp`, `phone`, `pos`)
- `type` - Filter by menu type (`main`, `breakfast`, `lunch`, `dinner`, `drinks`, `desserts`, `seasonal`)

### Menu Details
**GET** `/api/v1/menus/{id}`

Get menu details with all associated products.

**Query Parameters:**
- `channel` - Filter products by channel

### Create Menu
**POST** `/api/v1/menus` 🔒

Create a new menu.

**Request Body:**
```json
{
  "restaurant_id": 1,
  "name": {
    "en": "Main Menu",
    "ru": "Основное меню",
    "uz": "Asosiy menyu"
  },
  "description": {
    "en": "Our main menu",
    "ru": "Наше основное меню",
    "uz": "Bizning asosiy menyumiz"
  },
  "type": "main",
  "channels": ["web", "mobile", "pos"],
  "availability_hours": {
    "start": "09:00",
    "end": "22:00"
  },
  "sort_order": 1
}
```

---

## 🍕 Products

### List Products
**GET** `/api/v1/products`

Get paginated list of products with advanced filtering.

**Query Parameters:**
- `restaurant_id` - Filter by restaurant ID
- `category_id` - Filter by category ID
- `channel` - Filter by channel availability
- `featured` - Show only featured products (`true`/`false`)
- `search` - Search by product name or SKU

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "name": {
          "en": "Margherita Pizza",
          "ru": "Пицца Маргарита",
          "uz": "Margarita Pizza"
        },
        "prices": {
          "web": 12.99,
          "mobile": 12.99,
          "pos": 11.99
        },
        "restaurant": {
          "id": 1,
          "name": "Pizza Palace"
        },
        "category": {
          "id": 1,
          "name": "Pizza"
        }
      }
    ]
  },
  "message": "Products retrieved successfully"
}
```

### Product Details
**GET** `/api/v1/products/{id}`

Get detailed product information with channel-specific pricing.

**Query Parameters:**
- `channel` - Get price for specific channel

### Create Product
**POST** `/api/v1/products` 🔒

Create a new product with multilingual support.

**Request Body:**
```json
{
  "restaurant_id": 1,
  "category_id": 1,
  "name": {
    "en": "Margherita Pizza",
    "ru": "Пицца Маргарита",
    "uz": "Margarita Pizza"
  },
  "description": {
    "en": "Classic pizza with tomato and mozzarella",
    "ru": "Классическая пицца с томатами и моцареллой",
    "uz": "Pomidor va mozzarella bilan klassik pizza"
  },
  "type": "simple",
  "prices": {
    "web": 12.99,
    "mobile": 12.99,
    "pos": 11.99
  },
  "channels": ["web", "mobile", "pos"],
  "sku": "PIZZA-001",
  "images": ["https://example.com/pizza.jpg"],
  "calories": 250,
  "allergens": ["gluten", "dairy"],
  "ingredients": ["tomato", "mozzarella", "basil"]
}
```

---

## 📦 Orders

### List Orders
**GET** `/api/v1/orders` 🔒

Get paginated list of orders with filtering options.

**Query Parameters:**
- `restaurant_id` - Filter by restaurant ID
- `status` - Filter by order status
- `channel` - Filter by order channel
- `date_from` - Filter orders from date (YYYY-MM-DD)
- `date_to` - Filter orders to date (YYYY-MM-DD)

**Order Statuses:**
- `pending` - Order placed, awaiting confirmation
- `confirmed` - Order confirmed by restaurant
- `preparing` - Order is being prepared
- `ready` - Order ready for pickup/delivery
- `out_for_delivery` - Order out for delivery
- `delivered` - Order completed
- `cancelled` - Order cancelled

### Create Order
**POST** `/api/v1/orders` 🔒

Create a new order with items.

**Request Body:**
```json
{
  "restaurant_id": 1,
  "channel": "web",
  "customer_info": {
    "name": "John Doe",
    "phone": "+998901234567",
    "email": "john@example.com"
  },
  "delivery_info": {
    "type": "pickup"
  },
  "items": [
    {
      "product_id": 1,
      "quantity": 2,
      "modifiers": [],
      "special_instructions": "No onions"
    }
  ],
  "notes": "Please call when ready"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "order_number": "PIZ-000001",
    "status": "pending",
    "total_amount": "23.98",
    "currency": "USD"
  },
  "message": "Order created successfully"
}
```

### Order Details
**GET** `/api/v1/orders/{id}` 🔒

Get complete order information including items.

### Update Order Status
**PUT** `/api/v1/orders/{id}/status` 🔒

Update order status (restaurant staff only).

**Request Body:**
```json
{
  "status": "confirmed",
  "cancellation_reason": "Customer request"
}
```

---

## 🌐 Multilingual Support

FoodHub supports **3 languages** with JSON field structure:

### Supported Languages
- **English (en)** - Primary language
- **Russian (ru)** - Secondary language  
- **Uzbek (uz)** - Local language

### Data Structure
All text fields use JSON format:
```json
{
  "name": {
    "en": "Margherita Pizza",
    "ru": "Пицца Маргарита", 
    "uz": "Margarita Pizza"
  },
  "description": {
    "en": "Classic pizza with tomato and mozzarella",
    "ru": "Классическая пицца с томатами и моцареллой",
    "uz": "Pomidor va mozzarella bilan klassik pizza"
  }
}
```

### Validation Rules
- All 3 languages are **required** for name fields
- Descriptions are optional but if provided, should include all languages
- Product creation requires all language variants

---

## 📊 Channel Support

FoodHub supports **6 different channels** for multi-platform ordering:

### Available Channels
- **`web`** - Web application
- **`mobile`** - Mobile application
- **`telegram`** - Telegram bot
- **`whatsapp`** - WhatsApp integration
- **`phone`** - Phone orders
- **`pos`** - Point of sale system

### Channel-Specific Features
- **Different pricing** per channel
- **Availability control** per channel
- **Menu visibility** per channel
- **Order tracking** by channel

### Usage Examples
```json
{
  "prices": {
    "web": 12.99,
    "mobile": 12.99,
    "pos": 11.99,
    "telegram": 13.50
  },
  "channels": ["web", "mobile", "pos"]
}
```

---

## 🔧 Setup & Installation

### Prerequisites
- PHP 8.3+
- Composer
- PostgreSQL
- Docker (optional)

### Installation Steps

1. **Clone repository**
```bash
git clone https://github.com/your-repo/foodhub.git
cd foodhub
```

2. **Install dependencies**
```bash
composer install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Database setup**
```bash
php artisan migrate
php artisan db:seed
```

5. **Passport setup**
```bash
php artisan passport:install
```

6. **Generate API documentation**
```bash
php artisan l5-swagger:generate
```

### Docker Setup (Alternative)
```bash
docker-compose up -d
```

### Testing API
```bash
# Register user
curl -X POST http://localhost/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com", 
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Get restaurants
curl -X GET http://localhost/api/v1/restaurants

# Create order (with token)
curl -X POST http://localhost/api/v1/orders \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "restaurant_id": 1,
    "channel": "web",
    "customer_info": {
      "name": "John Doe",
      "phone": "+998901234567"
    },
    "delivery_info": {"type": "pickup"},
    "items": [{"product_id": 1, "quantity": 1}]
  }'
```

---

## 📚 Additional Resources

### Interactive Documentation
- **Swagger UI**: http://localhost/api/documentation
- **Complete endpoint list** with request/response examples
- **Try it out** functionality for testing

### Key Features
- ✅ **OAuth2 Authentication** (Laravel Passport)
- ✅ **Multilingual Support** (EN/RU/UZ)
- ✅ **Multi-channel Architecture** 
- ✅ **Real-time Order Tracking**
- ✅ **Advanced Filtering & Search**
- ✅ **Comprehensive API Documentation**
- ✅ **Rate Limiting & Security**

### Architecture Highlights
- **JSON fields** for efficient multilingual data storage
- **Channel-based pricing** and availability
- **Historical order preservation** with complete product snapshots
- **Modular restaurant management** system
- **Scalable multi-tenant** architecture

---

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 📞 Support

- **Email**: admin@foodhub.uz
- **Documentation**: http://localhost/api/documentation
- **Issues**: [GitHub Issues](https://github.com/your-repo/foodhub/issues)

---

**Built with ❤️ using Laravel 12 & PHP 8.3**
