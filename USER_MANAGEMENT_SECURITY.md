# 🔐 **FoodHub User Management & Security**

## 🔐 **Super Admin Creation & Security**

### ✅ **SECURE Super Admin Creation**

Super-admin users can now be created via **API** by other super-admin users for admin panel!

#### **API Creation (RECOMMENDED for Admin Panel):**
```bash
POST /api/v1/users
{
  "name": "Super Administrator",
  "email": "superadmin@foodhub.com", 
  "password": "superadmin123",
  "phone": "+998901234999",
  "role": "super-admin",
  "status": "active"
}
# ✅ Requires: super-admin Bearer token
# ✅ Full admin panel support
```

#### **Console Command (Initial Setup):**
```bash
# Interactive mode
php artisan user:create-super-admin

# With parameters
php artisan user:create-super-admin \
  --name="System Administrator" \
  --email="admin@foodhub.com" \
  --password="secure_password_123" \
  --phone="+998901234567"
```

#### **Database Seeder (Development only):**
```bash
php artisan db:seed --class=UserSeeder
# Creates: admin@foodhub.com / admin123
```

### 🎯 **Role Hierarchy & Creation Matrix**

| Current User Role | Can Create These Roles | Cannot Create | Notes |
|-------------------|------------------------|---------------|-------|
| **🔥 super-admin** | `super-admin`<br/>`admin`<br/>`restaurant-owner`<br/>`restaurant-manager`<br/>`kitchen-staff`<br/>`cashier`<br/>`call-center-operator`<br/>`courier`<br/>`customer` | None | 🚀 Full system access via API |
| **⚡ admin** | `restaurant-owner`<br/>`restaurant-manager`<br/>`kitchen-staff`<br/>`cashier`<br/>`call-center-operator`<br/>`courier`<br/>`customer` | `super-admin`<br/>`admin` | 🏢 System administration (manages restaurants) |
| **👑 restaurant-owner** | `restaurant-manager`<br/>`kitchen-staff`<br/>`cashier`<br/>`call-center-operator`<br/>`courier`<br/>`customer` | `super-admin`<br/>`admin`<br/>`restaurant-owner` | 🏢 Can only create staff for their restaurant |
| **👨‍💼 restaurant-manager** | ❌ None | All | 🚫 Cannot create users |
| **Other roles** | ❌ None | All | 🚫 Cannot create users |

### ❌ **FORBIDDEN Operations**

1. **Admin cannot create super-admin or admin:**
   ```bash
   POST /api/v1/users {"role": "super-admin"}  # 403 for admin
   POST /api/v1/users {"role": "admin"}        # 403 for admin
   ```

2. **Restaurant owners cannot create admin-level roles:**
   ```bash
   POST /api/v1/users {"role": "admin"}           # 403 for restaurant-owner
   POST /api/v1/users {"role": "restaurant-owner"} # 403 for restaurant-owner
   ```

---

## 📋 **Super Admin Protection**

### ✅ **Protected Operations**
Super Admin users (`super-admin` role) защищены от:

1. **Удаления** - `DELETE /api/v1/users/{user}`
   ```php
   if ($user->hasRole('super-admin')) {
       return response()->json([
           'success' => false,
           'message' => 'Cannot delete super admin user'
       ], 400);
   }
   ```

2. **Изменения ролей** - `PUT /api/v1/users/{user}/roles`
   ```php
   if ($user->hasRole('super-admin')) {
       return response()->json([
           'success' => false,
           'message' => 'Cannot change super admin roles'
       ], 400);
   }
   ```

3. **Изменения статуса** - `PUT /api/v1/users/{user}/status`
   ```php
   if ($user->hasRole('super-admin')) {
       return response()->json([
           'success' => false,
           'message' => 'Cannot change super admin status'
       ], 400);
   }
   ```

4. **Назначения super-admin роли** через API
   ```php
   if (in_array('super-admin', $validated['roles'])) {
       return response()->json([
           'success' => false,
           'message' => 'Cannot assign super-admin role through this endpoint'
       ], 400);
   }
   ```

---

## 👤 **User Profile Management**

### 🔄 **Authentication Endpoints**

#### **1. Update Profile** - `PUT /api/v1/auth/profile`
```bash
curl -X PUT http://localhost/api/v1/auth/profile \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe Updated",
    "phone": "+998901234568",
    "email": "john.updated@example.com", 
    "restaurant_id": 1
  }'
```

**Ограничения:**
- `restaurant_id` - только **Super Admin**, **Admin** или **Restaurant Owner**
- Остальные поля доступны всем авторизованным пользователям

#### **2. Change Password** - `PUT /api/v1/auth/password`
```bash
curl -X PUT http://localhost/api/v1/users/5/password \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'
```

**Требования:**
- Обязательна проверка текущего пароля
- Минимум 8 символов для нового пароля
- Подтверждение пароля

#### **3. Refresh Token** - `POST /api/v1/auth/refresh`
- Отзывает текущий токен
- Создает новый токен
- Возвращает новый Bearer token

---

## 🛡️ **Role-Based Access Control**

### **Security Architecture**
**Все проверки прав доступа выполняются в КОНТРОЛЛЕРАХ, а не middleware:**

```php
// Проверка в начале каждого метода контроллера
if (!$request->user()->hasAnyRole(['super-admin', 'restaurant-owner'])) {
    return response()->json([
        'success' => false,
        'message' => 'Insufficient permissions'
    ], 403);
}
```

### **Updated Role Hierarchy & Creation Permissions**

⚠️ **UPDATED:** Refer to the "Role Hierarchy & Creation Matrix" section above for current permissions

### **Access Control Implementation**

#### **User Management** `/api/v1/users/*`
```php
// Просмотр пользователей: super-admin, admin, restaurant-owner, restaurant-manager
if (!$request->user()->hasAnyRole(['super-admin', 'admin', 'restaurant-owner', 'restaurant-manager'])) {
    return response()->json(['success' => false, 'message' => 'Insufficient permissions'], 403);
}

// Создание пользователей: super-admin, admin, restaurant-owner
if (!$request->user()->hasAnyRole(['super-admin', 'admin', 'restaurant-owner'])) {
    return response()->json(['success' => false, 'message' => 'Insufficient permissions'], 403);
}

// Изменение ролей: только super-admin
if (!$request->user()->hasRole('super-admin')) {
    return response()->json(['success' => false, 'message' => 'Only super admin can change roles'], 403);
}

// Удаление пользователей: super-admin и admin
if (!auth()->user()->hasAnyRole(['super-admin', 'admin'])) {
    return response()->json(['success' => false, 'message' => 'Only super admin or admin can delete users'], 403);
}
```

#### **Content Management** (Categories, Menus, Products)
```php
// Создание/Обновление/Удаление: проверки в контроллерах
// Каждый контроллер проверяет права доступа для своих операций
```

#### **Restaurant Management** `/api/v1/restaurants/*`
```php
// Проверки выполняются в RestaurantController
// Create restaurant: super-admin only
// Update restaurant: super-admin, restaurant-owner
// Delete restaurant: super-admin only
```

#### **API Endpoints Access Control**

#### **User Creation** - `POST /api/v1/users`
```php
// Проверка прав доступа - super-admin, admin, restaurant-owner
if (!$request->user()->hasAnyRole(['super-admin', 'admin', 'restaurant-owner'])) {
    return 403; // Forbidden
}

// Super-admin может создавать super-admin через API
if ($role === 'super-admin') {
    if (!$request->user()->hasRole('super-admin')) {
        return 403; // Only super-admin can create super-admin
    }
}

// Admin может создавать restaurant-level роли
if ($user->hasRole('admin') && in_array($role, ['super-admin', 'admin'])) {
    return 403; // Admin cannot create super-admin or admin
}

// Ограничения для restaurant-owner остаются теми же
if ($user->hasRole('restaurant-owner') && !in_array($role, $allowedRoles)) {
    return 403; // Restaurant owners have limited role creation
}
```

#### **Role Viewing** - `GET /api/v1/users/roles-permissions`
- **Super Admin**: Видит ВСЕ роли включая `super-admin` (для управления через API)
- **Admin**: Видит роли кроме `super-admin` и `admin`  
- **Restaurant Owner**: Видит только роли которые может назначать
- **Others**: 403 Forbidden

---

## 🚀 **API Usage Examples**

### **Create Super Admin via API**
```bash
curl -X POST http://localhost/api/v1/users \
  -H "Authorization: Bearer {super_admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Super Administrator",
    "email": "superadmin2@foodhub.com",
    "password": "superadmin123",
    "phone": "+998901234999",
    "role": "super-admin",
    "status": "active"
  }'
```

### **Create Admin User via API**
```bash
curl -X POST http://localhost/api/v1/users \
  -H "Authorization: Bearer {super_admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "System Admin",
    "email": "admin@foodhub.com",
    "password": "admin123",
    "phone": "+998901234888",
    "role": "admin",
    "status": "active"
  }'
```

### **Admin Profile Update** (with restaurant change)
```bash
curl -X PUT http://localhost/api/v1/auth/profile \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe Updated",
    "phone": "+998901234568",
    "email": "john.updated@example.com", 
    "restaurant_id": 1
  }'
```

### **Admin Password Reset** (no current password required)
```bash
curl -X PUT http://localhost/api/v1/users/5/password \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'
```

---

## ⚡ **Key Security Features**

1. **🔥 API Super-Admin Creation** - Super-admin может создавать других super-admin через API
2. **⚡ Admin Role** - Новая роль admin для управления ресторанами и владельцами  
3. **🔒 Super Admin Protection** - Нельзя удалить или изменить роли/статус
4. **🎯 Controller-Based Permissions** - Проверки прав доступа в каждом методе контроллера
5. **🏢 Restaurant Scope** - Владельцы ресторанов видят только своих пользователей
6. **🚪 Token Management** - Безопасное обновление токенов

---

## 📊 **Complete User Management API**

**Всего 47+ endpoints** для полного управления системой:
- ✅ **10 User Management** endpoints (с поддержкой admin роли)
- ✅ **5 Authentication** endpoints  
- ✅ **8 Restaurant** endpoints
- ✅ **10 Category** endpoints
- ✅ **8 Menu** endpoints
- ✅ **8 Product** endpoints
- ✅ **6 Order** endpoints
- ✅ **4 Dashboard** endpoints

## 🏗️ **Architecture Benefits**

### **Full Admin Panel Support**
✅ **API создание super-admin** - Полная поддержка админ-панели  
✅ **Роль admin** - Гибкая система ролей с промежуточным уровнем  
✅ **Контроллерная безопасность** - Все проверки в контроллерах  
✅ **Консольная команда сохранена** - Для первоначального создания  

**🚀 Готово для продакшена с полной поддержкой админ-панели!**