# Modelagem do Banco de Dados - Estacionamento Uby

## 📊 Diagrama Entidade-Relacionamento

```
┌─────────────────┐         ┌──────────────────┐
│   operators     │         │    customers     │
├─────────────────┤         ├──────────────────┤
│ id (PK)         │         │ id (PK)          │
│ name            │         │ name             │
│ cpf (UNIQUE)    │         │ cpf (UNIQUE)     │
│ email (UNIQUE)  │         │ rg               │
│ password        │         │ email (UNIQUE)   │
│ email_verified  │         │ password         │
│ created_at      │         │ email_verified   │
│ updated_at      │         │ address_*        │
└─────────────────┘         │ created_at       │
                            │ updated_at       │
                            └──────────────────┘
                                     │
                                     │ 1:N
                                     ▼
                            ┌──────────────────┐
                            │    vehicles      │
                            ├──────────────────┤
                            │ id (PK)          │
                            │ customer_id (FK) │
                            │ plate (UNIQUE)   │
                            │ model            │
                            │ color            │
                            │ year             │
                            │ created_at       │
                            │ updated_at       │
                            └──────────────────┘

┌─────────────────┐
│ parking_spots   │
├─────────────────┤
│ id (PK)         │
│ operator_id(FK) │
│ number (UNIQUE) │
│ hourly_price    │
│ width           │
│ length          │
│ status (ENUM)   │
│ created_at      │
│ updated_at      │
└─────────────────┘
         │
         │ 1:N
         ▼
┌─────────────────┐         ┌──────────────────┐
│  reservations   │──N:1───▶│    customers     │
├─────────────────┤         └──────────────────┘
│ id (PK)         │         
│ customer_id(FK) │         ┌──────────────────┐
│ vehicle_id (FK) │────N:1─▶│    vehicles      │
│ spot_id (FK)    │         └──────────────────┘
│ entry_time      │
│ exit_time       │
│ status (ENUM)   │
│ created_at      │
│ updated_at      │
└─────────────────┘
         │
         │ 1:1
         ▼
┌─────────────────┐
│    payments     │
├─────────────────┤
│ id (PK)         │
│ reservation(FK) │
│ amount          │
│ hours_parked    │
│ status (ENUM)   │
│ paid_at         │
│ created_at      │
│ updated_at      │
└─────────────────┘

┌─────────────────┐         ┌──────────────────┐
│ chat_sessions   │──N:1───▶│    customers     │
├─────────────────┤         └──────────────────┘
│ id (PK)         │         
│ customer_id(FK) │         ┌──────────────────┐
│ operator_id(FK) │────N:1─▶│    operators     │
│ status (ENUM)   │         └──────────────────┘
│ started_at      │
│ ended_at        │
│ created_at      │
│ updated_at      │
└─────────────────┘
         │
         │ 1:N
         ▼
┌─────────────────┐
│ chat_messages   │
├─────────────────┤
│ id (PK)         │
│ session_id (FK) │
│ sender_type     │ (operator/customer)
│ sender_id       │
│ message         │
│ read_at         │
│ created_at      │
└─────────────────┘
```

## 🗃️ Detalhamento das Tabelas

### 1. operators (Operadores do Estacionamento)
```sql
CREATE TABLE operators (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_operators_email (email),
    INDEX idx_operators_cpf (cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Campos:**
- `id`: Identificador único
- `name`: Nome completo do operador
- `cpf`: CPF formatado (XXX.XXX.XXX-XX)
- `email`: Email único para login
- `password`: Hash bcrypt da senha
- `email_verified_at`: Data/hora de verificação do email

**Índices:**
- `email`, `cpf`: Buscas frequentes por login e validação

---

### 2. customers (Clientes)
```sql
CREATE TABLE customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    rg VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    
    -- Endereço
    address_zipcode VARCHAR(9) NOT NULL,
    address_street VARCHAR(255) NOT NULL,
    address_number VARCHAR(20) NOT NULL,
    address_complement VARCHAR(255) NULL,
    address_neighborhood VARCHAR(255) NOT NULL,
    address_city VARCHAR(255) NOT NULL,
    address_state VARCHAR(2) NOT NULL,
    
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_customers_email (email),
    INDEX idx_customers_cpf (cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Campos:**
- Dados pessoais: `name`, `cpf`, `rg`, `email`
- Endereço completo para entrega de correspondência
- `address_zipcode`: CEP para validação via ViaCEP API

**Regras de Negócio:**
- Email único para login
- CPF validado e único
- Endereço validado via API ViaCEP

---

### 3. vehicles (Veículos dos Clientes)
```sql
CREATE TABLE vehicles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    plate VARCHAR(10) NOT NULL UNIQUE,
    model VARCHAR(255) NOT NULL,
    color VARCHAR(50) NOT NULL,
    year SMALLINT UNSIGNED NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_vehicles_customer (customer_id),
    INDEX idx_vehicles_plate (plate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Relacionamentos:**
- **N:1** com `customers` (um cliente pode ter múltiplos veículos)
- `ON DELETE CASCADE`: Remove veículos ao deletar cliente

**Regras de Negócio:**
- Placa única no sistema (ABC-1234 ou ABC1D23)
- Ano entre 1900 e ano atual + 1
- Cliente pode ter múltiplos veículos

---

### 4. parking_spots (Vagas de Estacionamento)
```sql
CREATE TABLE parking_spots (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    operator_id BIGINT UNSIGNED NOT NULL,
    number VARCHAR(10) NOT NULL UNIQUE,
    hourly_price DECIMAL(8, 2) NOT NULL,
    width DECIMAL(5, 2) NOT NULL COMMENT 'Largura em metros',
    length DECIMAL(5, 2) NOT NULL COMMENT 'Comprimento em metros',
    status ENUM('available', 'occupied', 'maintenance', 'reserved') DEFAULT 'available',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (operator_id) REFERENCES operators(id) ON DELETE RESTRICT,
    INDEX idx_spots_status (status),
    INDEX idx_spots_operator (operator_id),
    INDEX idx_spots_number (number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Status:**
- `available`: Disponível para reserva
- `occupied`: Ocupada por veículo
- `maintenance`: Em manutenção (não disponível)
- `reserved`: Reservada (aguardando entrada do veículo)

**Regras de Negócio:**
- Número da vaga único (ex: A1, B23)
- Preço por hora sempre positivo
- Dimensões em metros (validar > 0)
- Operador que cadastrou pode editar

---

### 5. reservations (Reservas/Estadias)
```sql
CREATE TABLE reservations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    vehicle_id BIGINT UNSIGNED NOT NULL,
    parking_spot_id BIGINT UNSIGNED NOT NULL,
    entry_time TIMESTAMP NOT NULL,
    exit_time TIMESTAMP NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE RESTRICT,
    FOREIGN KEY (parking_spot_id) REFERENCES parking_spots(id) ON DELETE RESTRICT,
    
    INDEX idx_reservations_customer (customer_id),
    INDEX idx_reservations_vehicle (vehicle_id),
    INDEX idx_reservations_spot (parking_spot_id),
    INDEX idx_reservations_status (status),
    INDEX idx_reservations_entry (entry_time),
    
    CONSTRAINT chk_exit_after_entry CHECK (exit_time IS NULL OR exit_time > entry_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Status:**
- `active`: Veículo estacionado atualmente
- `completed`: Veículo já saiu, pagamento concluído
- `cancelled`: Reserva cancelada

**Regras de Negócio:**
- Vaga só pode ter 1 reserva ativa por vez
- `exit_time` sempre posterior a `entry_time`
- Ao criar reserva, atualizar status da vaga para 'occupied'
- Ao finalizar, calcular pagamento

**Constraints:**
- `ON DELETE RESTRICT`: Não permite deletar cliente/veículo/vaga com reservas

---

### 6. payments (Pagamentos)
```sql
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reservation_id BIGINT UNSIGNED NOT NULL UNIQUE,
    amount DECIMAL(10, 2) NOT NULL,
    hours_parked DECIMAL(10, 2) NOT NULL COMMENT 'Horas totais (pode ser decimal)',
    status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE RESTRICT,
    INDEX idx_payments_reservation (reservation_id),
    INDEX idx_payments_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Relacionamento:**
- **1:1** com `reservations` (uma reserva = um pagamento)

**Cálculo:**
- `amount = hours_parked * parking_spot.hourly_price`
- Arredondar minutos para cima (ex: 2h15min = 3h)

**Status:**
- `pending`: Aguardando pagamento
- `paid`: Pago
- `cancelled`: Cancelado

---

### 7. chat_sessions (Sessões de Chat)
```sql
CREATE TABLE chat_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,
    operator_id BIGINT UNSIGNED NULL,
    status ENUM('waiting', 'active', 'closed') DEFAULT 'waiting',
    started_at TIMESTAMP NULL,
    ended_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (operator_id) REFERENCES operators(id) ON DELETE SET NULL,
    
    INDEX idx_chat_customer (customer_id),
    INDEX idx_chat_operator (operator_id),
    INDEX idx_chat_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Status:**
- `waiting`: Cliente aguardando operador
- `active`: Chat ativo com operador
- `closed`: Sessão encerrada

**Fluxo:**
1. Cliente inicia chat (status: waiting)
2. Operador aceita (status: active, define `operator_id`)
3. Chat é encerrado (status: closed, define `ended_at`)

---

### 8. chat_messages (Mensagens do Chat)
```sql
CREATE TABLE chat_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chat_session_id BIGINT UNSIGNED NOT NULL,
    sender_type ENUM('customer', 'operator') NOT NULL,
    sender_id BIGINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (chat_session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE,
    
    INDEX idx_messages_session (chat_session_id),
    INDEX idx_messages_sender (sender_type, sender_id),
    INDEX idx_messages_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Campos:**
- `sender_type`: Tipo do remetente (customer ou operator)
- `sender_id`: ID do customer ou operator que enviou
- `read_at`: Timestamp de quando foi lida (para notificações)

**Relacionamento:**
- **N:1** com `chat_sessions`

---

## 🔐 Índices e Performance

### Índices Estratégicos:
1. **Foreign Keys:** Sempre indexadas automaticamente
2. **Campos de busca frequente:** email, cpf, plate, number
3. **Campos de filtro:** status, entry_time
4. **Campos de ordenação:** created_at nos chats

### Otimizações:
- **InnoDB:** Suporta transações e constraints
- **UTF8MB4:** Suporta emojis nas mensagens
- **Decimal:** Para valores monetários (evita problemas de arredondamento)
- **ENUM:** Para status fixos (performance e integridade)

---

## 📋 Constraints e Validações

### Regras de Integridade:
1. **Unicidade:**
   - CPF de operators e customers
   - Email de operators e customers
   - Placa de veículos
   - Número de vagas

2. **Relacionamentos:**
   - Cascade: vehicles (deleta veículos ao deletar cliente)
   - Restrict: reservations (não permite deletar com reservas ativas)
   - Set NULL: chat operators (mantém histórico se operador for deletado)

3. **Check Constraints:**
   - `exit_time > entry_time`
   - `year <= YEAR(CURRENT_DATE) + 1`
   - `hourly_price > 0`
   - `width > 0 AND length > 0`

---

## 🎯 Queries Otimizadas Esperadas

### 1. Buscar vagas disponíveis:
```sql
SELECT * FROM parking_spots 
WHERE status = 'available' 
ORDER BY number ASC;
```
**Índice usado:** `idx_spots_status`

### 2. Histórico de reservas do cliente:
```sql
SELECT r.*, ps.number, v.plate, p.amount
FROM reservations r
INNER JOIN parking_spots ps ON r.parking_spot_id = ps.id
INNER JOIN vehicles v ON r.vehicle_id = v.id
LEFT JOIN payments p ON r.id = p.reservation_id
WHERE r.customer_id = ?
ORDER BY r.created_at DESC;
```
**Índice usado:** `idx_reservations_customer`

### 3. Reservas ativas por vaga:
```sql
SELECT * FROM reservations 
WHERE parking_spot_id = ? 
  AND status = 'active'
LIMIT 1;
```
**Índices usados:** `idx_reservations_spot`, `idx_reservations_status`

---

## 📊 Estimativa de Volumetria

### Cenário Inicial (Muzambinho):
- **Operadores:** ~5-10
- **Clientes:** ~500-1000
- **Veículos:** ~800-1500
- **Vagas:** ~50-100
- **Reservas/mês:** ~2000-5000
- **Mensagens chat/dia:** ~100-500

### Crescimento Esperado:
- Reservas crescem linearmente
- Mensagens podem ter picos
- **Particionamento futuro:** Por data nas tabelas de reservations e chat_messages

---

## ✅ Checklist de Implementação

- [ ] Criar migrations na ordem correta (dependências)
- [ ] Adicionar índices em todas as migrations
- [ ] Criar seeders para desenvolvimento
- [ ] Implementar factories para testes
- [ ] Validar constraints no banco E na aplicação
- [ ] Documentar relacionamentos no README
- [ ] Criar diagrama visual (draw.io, dbdiagram.io)

---

**Próximo passo:** Implementar as migrations no Laravel seguindo esta modelagem.
