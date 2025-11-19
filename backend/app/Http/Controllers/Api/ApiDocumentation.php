<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * Arquivo dedicado exclusivamente para documentação Swagger/OpenAPI
 * NÃO adicionar lógica de negócio aqui
 */

/**
 * @OA\Post(
 *     path="/api/v1/customers/register",
 *     summary="Registrar novo cliente",
 *     description="Cria uma nova conta de cliente no sistema com endereço completo",
 *     operationId="registerCustomer",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Dados do cliente",
 *         @OA\JsonContent(
 *             required={"name","email","cpf","password","password_confirmation"},
 *             @OA\Property(property="name", type="string", example="João Silva", description="Nome completo"),
 *             @OA\Property(property="email", type="string", format="email", example="joao.silva@email.com", description="Email único"),
 *             @OA\Property(property="cpf", type="string", example="12345678900", description="CPF com 11 dígitos (sem pontos)"),
 *             @OA\Property(property="rg", type="string", example="123456789", description="RG (opcional)"),
 *             @OA\Property(property="password", type="string", format="password", example="password123", description="Senha (mínimo 8 caracteres)"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123", description="Confirmação da senha"),
 *             @OA\Property(property="phone", type="string", example="11999999999", description="Telefone (opcional)"),
 *             @OA\Property(property="zip_code", type="string", example="01310100", description="CEP com 8 dígitos"),
 *             @OA\Property(property="street", type="string", example="Av Paulista", description="Logradouro"),
 *             @OA\Property(property="number", type="string", example="1000", description="Número"),
 *             @OA\Property(property="complement", type="string", example="Apto 101", description="Complemento (opcional)"),
 *             @OA\Property(property="neighborhood", type="string", example="Bela Vista", description="Bairro"),
 *             @OA\Property(property="city", type="string", example="São Paulo", description="Cidade"),
 *             @OA\Property(property="state", type="string", example="SP", description="Estado (2 letras)")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Cliente registrado com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="token", type="string", example="1|abc123...", description="Token de autenticação Sanctum"),
 *             @OA\Property(
 *                 property="user",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="João Silva"),
 *                 @OA\Property(property="email", type="string", example="joao.silva@email.com"),
 *                 @OA\Property(property="cpf", type="string", example="12345678900"),
 *                 @OA\Property(property="phone", type="string", example="11999999999"),
 *                 @OA\Property(property="type", type="string", example="customer")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=422, description="Dados inválidos")
 * )
 * 
 * @OA\Get(
 *     path="/api/v1/customers/me",
 *     summary="Obter dados do cliente autenticado",
 *     description="Retorna os dados completos do cliente logado incluindo endereço",
 *     operationId="getCurrentCustomer",
 *     tags={"Customers"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Dados do cliente",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="João Silva"),
 *                 @OA\Property(property="email", type="string", example="joao.silva@email.com"),
 *                 @OA\Property(property="cpf", type="string", example="12345678900"),
 *                 @OA\Property(property="rg", type="string", example="123456789"),
 *                 @OA\Property(property="phone", type="string", example="11999999999"),
 *                 @OA\Property(property="type", type="string", example="customer"),
 *                 @OA\Property(
 *                     property="address",
 *                     type="object",
 *                     @OA\Property(property="zip_code", type="string", example="01310100"),
 *                     @OA\Property(property="street", type="string", example="Av Paulista"),
 *                     @OA\Property(property="number", type="string", example="1000"),
 *                     @OA\Property(property="complement", type="string", nullable=true),
 *                     @OA\Property(property="neighborhood", type="string", example="Bela Vista"),
 *                     @OA\Property(property="city", type="string", example="São Paulo"),
 *                     @OA\Property(property="state", type="string", example="SP")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Não autenticado")
 * )
 * 
 * @OA\Post(
 *     path="/api/v1/vehicles",
 *     summary="Criar novo veículo",
 *     description="Registra um novo veículo para o cliente autenticado",
 *     operationId="createVehicle",
 *     tags={"Vehicles"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"customer_id","license_plate","brand","model","color","type"},
 *             @OA\Property(property="customer_id", type="integer", example=1),
 *             @OA\Property(property="license_plate", type="string", example="ABC1D234", description="Placa no formato Mercosul"),
 *             @OA\Property(property="brand", type="string", example="Toyota", description="Marca do veículo"),
 *             @OA\Property(property="model", type="string", example="Corolla"),
 *             @OA\Property(property="color", type="string", example="Prata"),
 *             @OA\Property(property="type", type="string", enum={"car","motorcycle","truck"}, example="car")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Veículo criado com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="customer_id", type="integer", example=1),
 *                 @OA\Property(property="license_plate", type="string", example="ABC1D234"),
 *                 @OA\Property(property="brand", type="string", example="Toyota"),
 *                 @OA\Property(property="model", type="string", example="Corolla"),
 *                 @OA\Property(property="color", type="string", example="Prata"),
 *                 @OA\Property(property="type", type="string", example="car"),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Não autenticado"),
 *     @OA\Response(response=422, description="Dados inválidos")
 * )
 * 
 * @OA\Get(
 *     path="/api/v1/vehicles",
 *     summary="Listar veículos do cliente",
 *     description="Retorna todos os veículos do cliente autenticado",
 *     operationId="listVehicles",
 *     tags={"Vehicles"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Lista de veículos",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
 *         )
 *     )
 * )
 * 
 * @OA\Post(
 *     path="/api/v1/parking-spots",
 *     summary="Criar nova vaga",
 *     description="Cria uma nova vaga de estacionamento. Valores padrão: hourly_price=5.00, width=2.50, length=5.00",
 *     operationId="createParkingSpot",
 *     tags={"Parking Spots"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"number","type"},
 *             @OA\Property(property="number", type="string", example="A01", description="Número/identificação da vaga"),
 *             @OA\Property(property="type", type="string", enum={"regular","vip","disabled"}, example="regular"),
 *             @OA\Property(property="hourly_price", type="number", format="float", example=5.00, description="Valor por hora (padrão: 5.00)"),
 *             @OA\Property(property="width", type="number", format="float", example=2.50, description="Largura em metros (padrão: 2.50)"),
 *             @OA\Property(property="length", type="number", format="float", example=5.00, description="Comprimento em metros (padrão: 5.00)"),
 *             @OA\Property(property="operator_id", type="integer", example=1, nullable=true, description="ID do operador (opcional)")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Vaga criada com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="number", type="string", example="A01"),
 *                 @OA\Property(property="type", type="string", example="regular"),
 *                 @OA\Property(property="status", type="string", example="available"),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time")
 *             )
 *         )
 *     )
 * )
 * 
 * @OA\Get(
 *     path="/api/v1/parking-spots",
 *     summary="Listar vagas",
 *     description="Lista vagas de estacionamento. Pode filtrar por status (available, occupied, reserved)",
 *     operationId="listParkingSpots",
 *     tags={"Parking Spots"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         description="Filtrar por status",
 *         required=false,
 *         @OA\Schema(type="string", enum={"available","occupied","reserved"})
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Lista de vagas",
 *         @OA\JsonContent(
 *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
 *         )
 *     )
 * )
 * 
 * @OA\Post(
 *     path="/api/v1/reservations",
 *     summary="Criar reserva",
 *     description="Cria uma nova reserva de vaga. A vaga fica com status occupied e total_amount fica null até completar",
 *     operationId="createReservation",
 *     tags={"Reservations"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"customer_id","vehicle_id","parking_spot_id","entry_time"},
 *             @OA\Property(property="customer_id", type="integer", example=1),
 *             @OA\Property(property="vehicle_id", type="integer", example=1),
 *             @OA\Property(property="parking_spot_id", type="integer", example=1),
 *             @OA\Property(property="entry_time", type="string", format="date-time", example="2025-11-19T12:00:00", description="Data/hora de entrada no formato ISO 8601")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Reserva criada",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="vehicle_id", type="integer", example=1),
 *                 @OA\Property(property="entry_time", type="string", format="date-time"),
 *                 @OA\Property(property="exit_time", type="string", format="date-time", nullable=true),
 *                 @OA\Property(property="expected_exit_time", type="string", format="date-time", nullable=true),
 *                 @OA\Property(property="total_amount", type="string", nullable=true, description="Valor total (null até completar)"),
 *                 @OA\Property(property="status", type="string", example="active"),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time")
 *             )
 *         )
 *     )
 * )
 * 
 * @OA\Post(
 *     path="/api/v1/reservations/{id}/complete",
 *     summary="Completar reserva",
 *     description="Finaliza a reserva calculando o valor total. Cálculo: R$5/hora + R$1 por bloco de 15min extras. A vaga volta para status available",
 *     operationId="completeReservation",
 *     tags={"Reservations"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID da reserva",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"exit_time"},
 *             @OA\Property(property="exit_time", type="string", format="date-time", example="2025-11-19T14:00:00", description="Data/hora de saída")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Reserva completada com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="vehicle_id", type="integer", example=1),
 *                 @OA\Property(property="entry_time", type="string", format="date-time"),
 *                 @OA\Property(property="exit_time", type="string", format="date-time"),
 *                 @OA\Property(property="total_amount", type="string", example="10.00", description="Valor total calculado"),
 *                 @OA\Property(property="status", type="string", example="completed"),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time")
 *             )
 *         )
 *     )
 * )
 * 
 * @OA\Post(
 *     path="/api/v1/payments",
 *     summary="Criar pagamento",
 *     description="Cria um pagamento para uma reserva completada",
 *     operationId="createPayment",
 *     tags={"Payments"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"reservation_id","amount","payment_method"},
 *             @OA\Property(property="reservation_id", type="integer", example=1),
 *             @OA\Property(property="amount", type="number", format="float", example=10.00, description="Valor do pagamento"),
 *             @OA\Property(property="payment_method", type="string", enum={"credit_card","debit_card","pix","cash","others"}, example="credit_card"),
 *             @OA\Property(property="status", type="string", enum={"pending","paid","cancelled"}, example="pending")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Pagamento criado",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="amount", type="string", example="10.00"),
 *                 @OA\Property(property="payment_method", type="string", example="credit_card"),
 *                 @OA\Property(property="status", type="string", example="pending"),
 *                 @OA\Property(property="paid_at", type="string", format="date-time", nullable=true),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time")
 *             )
 *         )
 *     )
 * )
 * 
 * @OA\Post(
 *     path="/api/v1/payments/{id}/mark-as-paid",
 *     summary="Marcar pagamento como pago",
 *     description="Atualiza o status do pagamento para paid e registra a data/hora do pagamento",
 *     operationId="markPaymentAsPaid",
 *     tags={"Payments"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID do pagamento",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Pagamento confirmado",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="amount", type="string", example="10.00"),
 *                 @OA\Property(property="payment_method", type="string", example="credit_card"),
 *                 @OA\Property(property="status", type="string", example="paid"),
 *                 @OA\Property(property="paid_at", type="string", format="date-time"),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time")
 *             )
 *         )
 *     )
 * )
 * 
 * @OA\Get(
 *     path="/api/v1/viacep/{zipcode}",
 *     summary="Consultar CEP via ViaCEP",
 *     description="Consulta dados de endereço através do CEP utilizando a API ViaCEP",
 *     operationId="getAddressByZipCode",
 *     tags={"Utils"},
 *     @OA\Parameter(
 *         name="zipcode",
 *         in="path",
 *         description="CEP com 8 dígitos (sem pontos ou hífen)",
 *         required=true,
 *         @OA\Schema(type="string", example="01310100")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Dados do endereço",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="zip_code", type="string", example="01310-100"),
 *                 @OA\Property(property="street", type="string", example="Avenida Paulista"),
 *                 @OA\Property(property="complement", type="string", example="de 612 a 1510 - lado par"),
 *                 @OA\Property(property="neighborhood", type="string", example="Bela Vista"),
 *                 @OA\Property(property="city", type="string", example="São Paulo"),
 *                 @OA\Property(property="state", type="string", example="SP"),
 *                 @OA\Property(property="ibge", type="string", example="3550308")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=404, description="CEP não encontrado")
 * )
 */
class ApiDocumentation extends Controller
{
    /**
     * Este controller existe APENAS para documentação Swagger.
     * NÃO adicione métodos ou lógica de negócio aqui.
     */
}
