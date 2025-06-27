# 🚀 **ПРОМПТ ДЛЯ ФРОНТЕНД ИИ: USER MANAGEMENT В АДМИНКЕ FOODHUB**

## 🎯 **Цель:** Создать полноценный UI для управления пользователями в админ-панели FoodHub

### **📍 Текущее состояние фронтенда:**
- ✅ Есть страницы: `auth/`, `dashboard/`, `products/`, `orders/`, `menus/`, `settings/`
- ❌ **НЕТ** страницы для управления пользователями (`users/`)
- ✅ API сервисы: `auth.service.ts`, `products.service.ts`, `orders.service.ts`, `dashboard.service.ts` 
- ❌ **НЕТ** `users.service.ts`

### **🔗 Готовые API Endpoints Backend (HTTP://localhost/api/v1):**

#### **User Management API (10 endpoints):**
```bash
# 1. Список пользователей (с фильтрами)
GET /api/v1/users?restaurant_id=1&role=cashier&status=active

# 2. Создание пользователя (включая super-admin через API!)
POST /api/v1/users
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "phone": "+998901234567",
  "role": "cashier",
  "restaurant_id": 1,
  "status": "active"
}

# 3. Детали пользователя
GET /api/v1/users/123

# 4. Обновление пользователя
PUT /api/v1/users/123
{
  "name": "John Updated",
  "phone": "+998901234568",
  "status": "active"
}

# 5. Изменение пароля (админское)
PUT /api/v1/users/123/password
{
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}

# 6. Обновление ролей (только super-admin)
PUT /api/v1/users/123/roles
{
  "roles": ["restaurant-manager", "cashier"]
}

# 7. Изменение статуса
PUT /api/v1/users/123/status
{
  "status": "suspended"
}

# 8. Удаление пользователя
DELETE /api/v1/users/123

# 9. Получение всех ролей/разрешений
GET /api/v1/users/roles-permissions

# 10. Пользователи по ресторану
GET /api/v1/restaurants/1/users
```

### **🔐 Система ролей (Иерархия):**
```typescript
interface RoleHierarchy {
  "super-admin": {
    canCreate: ["super-admin", "admin", "restaurant-owner", "restaurant-manager", "kitchen-staff", "cashier", "call-center-operator", "courier", "customer"];
    cannotCreate: [];
    description: "🔥 Полный доступ к системе";
  };
  "admin": {
    canCreate: ["restaurant-owner", "restaurant-manager", "kitchen-staff", "cashier", "call-center-operator", "courier", "customer"];
    cannotCreate: ["super-admin", "admin"];
    description: "⚡ Администрирование системы";
  };
  "restaurant-owner": {
    canCreate: ["restaurant-manager", "kitchen-staff", "cashier", "call-center-operator", "courier", "customer"];
    cannotCreate: ["super-admin", "admin", "restaurant-owner"];
    description: "👑 Владелец ресторана";
  };
  "restaurant-manager": {
    canCreate: [];
    cannotCreate: ["все"];
    description: "👨‍💼 Менеджер ресторана";
  };
}
```

### **📋 Требуемые UI Компоненты:**

#### **1. Users List Page (`/users`)**
```typescript
interface UsersListProps {
  // Фильтры
  filters: {
    restaurant_id?: number;
    role?: string;
    status?: "active" | "inactive" | "suspended";
    search?: string;
  };
  
  // Функции
  onCreateUser: () => void;
  onEditUser: (userId: number) => void;
  onDeleteUser: (userId: number) => void;
  onChangeStatus: (userId: number, status: string) => void;
}

// Таблица с колонками:
// - Name (ссылка на детали)
// - Email
// - Phone  
// - Role (бейдж)
// - Restaurant (если есть)
// - Status (бейдж)
// - Last Login
// - Actions (Edit, Delete, Change Status)
```

#### **2. Create/Edit User Modal**
```typescript
interface UserFormData {
  name: string;
  email: string;
  password?: string; // только для создания
  phone?: string;
  role: string;
  restaurant_id?: number;
  status: "active" | "inactive" | "suspended";
}

// Поля:
// - Name (обязательно)
// - Email (обязательно, валидация)
// - Password (только при создании, минимум 8 символов)
// - Phone (необязательно)
// - Role (select с ограничениями по иерархии!)
// - Restaurant (select, только если роль требует)
// - Status (radio buttons: Active/Inactive/Suspended)
```

#### **3. User Details Page (`/users/:id`)**
```typescript
interface UserDetailsProps {
  user: User;
  onEdit: () => void;
  onChangePassword: () => void;
  onChangeRoles: () => void;
  onChangeStatus: () => void;
}

// Разделы:
// - Основная информация
// - Роли и разрешения
// - История активности
// - Безопасность (смена пароля)
```

#### **4. Change Password Modal**
```typescript
interface ChangePasswordProps {
  userId: number;
  isAdmin: boolean; // если true, не требует текущий пароль
}
```

#### **5. Manage Roles Modal**
```typescript
interface ManageRolesProps {
  userId: number;
  currentRoles: string[];
  availableRoles: string[]; // с учетом иерархии
}
```

### **🛠️ Технические требования:**

#### **1. Users Service (`src/services/users.service.ts`)**
```typescript
export class UsersService {
  static async getUsers(filters: UserFilters): Promise<PaginatedResponse<User>>;
  static async createUser(userData: CreateUserRequest): Promise<User>;
  static async getUserById(id: number): Promise<User>;
  static async updateUser(id: number, data: UpdateUserRequest): Promise<User>;
  static async changePassword(id: number, data: ChangePasswordRequest): Promise<void>;
  static async updateRoles(id: number, roles: string[]): Promise<User>;
  static async updateStatus(id: number, status: string): Promise<User>;
  static async deleteUser(id: number): Promise<void>;
  static async getRolesAndPermissions(): Promise<RolesPermissionsResponse>;
  static async getUsersByRestaurant(restaurantId: number): Promise<User[]>;
}
```

#### **2. Types (`src/types/users.types.ts`)**
```typescript
export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string;
  restaurant_id?: number;
  restaurant?: Restaurant;
  roles: Role[];
  status: "active" | "inactive" | "suspended";
  last_login_at?: string;
  created_at: string;
  updated_at: string;
}

export interface Role {
  id: number;
  name: string;
  permissions: Permission[];
}

export interface CreateUserRequest {
  name: string;
  email: string;
  password: string;
  phone?: string;
  role: string;
  restaurant_id?: number;
  status: string;
}
```

#### **3. UI Components:**
```typescript
// Переиспользуемые компоненты:
<UserStatusBadge status="active" />
<RoleBadge role="restaurant-manager" />
<UserActionsDropdown user={user} onAction={handleAction} />
<UserFilters filters={filters} onChange={setFilters} />
<UserFormModal isOpen={isOpen} user={editingUser} onClose={onClose} />
```

### **🎨 UI/UX Гайдлайны:**

#### **Цветовая схема ролей:**
```css
.super-admin { background: #dc2626; } /* Красный */
.admin { background: #7c3aed; }       /* Фиолетовый */
.restaurant-owner { background: #059669; } /* Зеленый */
.restaurant-manager { background: #0891b2; } /* Синий */
.kitchen-staff { background: #ea580c; }     /* Оранжевый */
.cashier { background: #9333ea; }           /* Пурпурный */
.customer { background: #6b7280; }          /* Серый */
```

#### **Статусы:**
```css
.status-active { background: #10b981; }     /* Зеленый */
.status-inactive { background: #6b7280; }   /* Серый */
.status-suspended { background: #ef4444; }  /* Красный */
```

### **🔒 Безопасность и разрешения:**

#### **Проверки на фронтенде:**
```typescript
// Проверка перед показом кнопки "Create Super Admin"
const canCreateSuperAdmin = currentUser.roles.includes('super-admin');

// Проверка доступных ролей для создания
const getAvailableRoles = (currentUserRole: string) => {
  const hierarchy = {
    'super-admin': ['super-admin', 'admin', 'restaurant-owner', 'restaurant-manager', 'kitchen-staff', 'cashier', 'customer'],
    'admin': ['restaurant-owner', 'restaurant-manager', 'kitchen-staff', 'cashier', 'customer'],
    'restaurant-owner': ['restaurant-manager', 'kitchen-staff', 'cashier', 'customer']
  };
  return hierarchy[currentUserRole] || [];
};
```

### **📊 Дополнительные фичи:**

#### **1. Массовые операции:**
- Выбор нескольких пользователей
- Массовое изменение статуса
- Массовое удаление (с подтверждением)

#### **2. Экспорт данных:**
- Экспорт списка пользователей в CSV/Excel
- Фильтрация перед экспортом

#### **3. Поиск и фильтрация:**
- Полнотекстовый поиск по имени/email
- Фильтр по ресторану
- Фильтр по роли
- Фильтр по статусу
- Фильтр по дате регистрации

### **🚀 Приоритет разработки:**

1. **Высокий приоритет:**
   - Users List Page
   - Create/Edit User Modal
   - Users Service
   - Basic CRUD операции

2. **Средний приоритет:**
   - User Details Page
   - Change Password functionality
   - Role management
   - Status management

3. **Низкий приоритет:**
   - Массовые операции
   - Экспорт данных
   - Расширенная фильтрация

### **🔗 Integration Points:**

#### **Навигация:**
Добавить в главное меню админки:
```typescript
{
  title: 'Users',
  icon: 'UsersIcon',
  path: '/users',
  roles: ['super-admin', 'admin', 'restaurant-owner'] // Доступ по ролям
}
```

#### **Демо пользователи (синхронизированы с бэкендом):**
```typescript
const demoUsers = [
  { name: 'Super Admin', email: 'admin@foodhub.com', password: 'admin123', role: 'super-admin' },
  { name: 'System Admin', email: 'sysadmin@foodhub.com', password: 'sysadmin123', role: 'admin' },
  { name: 'Restaurant Owner', email: 'owner@pizzapalace.com', password: 'owner123', role: 'restaurant-owner' },
  { name: 'Manager', email: 'manager@foodhub.com', password: 'manager123', role: 'restaurant-manager' },
  { name: 'Cashier', email: 'cashier@foodhub.com', password: 'cashier123', role: 'cashier' },
  { name: 'Kitchen Staff', email: 'staff@foodhub.com', password: 'staff123', role: 'kitchen-staff' },
  { name: 'Test User 1', email: 'testuser1@foodhub.com', password: 'test123', role: 'customer' },
  { name: 'Test User 2', email: 'testuser2@foodhub.com', password: 'test123', role: 'customer' }
];
```

---

## 💡 **Итоговая задача:**

Создать полноценную систему управления пользователями в AdminPanel с поддержкой:
- ✅ **Всех 10 API endpoints**
- ✅ **Системы ролей и разрешений** 
- ✅ **Создания super-admin через UI**
- ✅ **Безопасности и валидации**
- ✅ **Современного UI/UX**

**Backend готов на 100% - нужно только создать фронтенд!** 🚀 