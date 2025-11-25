import { httpClient } from './httpClient';
import type { ApiResponse, ParkingSpot, Reservation, Vehicle } from '../../domain/types';

export const parkingApi = {
  // Listar vagas disponíveis
  async getAvailableSpots(): Promise<ParkingSpot[]> {
    const response = await httpClient.get<ApiResponse<ParkingSpot[]>>('/parking-spots-available');
    return response.data.data;
  },

  // Buscar vaga por ID
  async getSpotById(id: number): Promise<ParkingSpot> {
    const response = await httpClient.get<ApiResponse<ParkingSpot>>(`/parking-spots/${id}`);
    return response.data.data;
  },

  // Criar reserva
  async createReservation(data: {
    parking_spot_id: number;
    vehicle_id: number;
  }): Promise<Reservation> {
    const payload = {
      ...data,
      entry_time: new Date().toISOString(),
    };
    const response = await httpClient.post<ApiResponse<Reservation>>('/reservations', payload);
    return response.data.data;
  },

  // Listar minhas reservas
  async getMyReservations(): Promise<Reservation[]> {
    const response = await httpClient.get<ApiResponse<Reservation[]>>('/reservations');
    return response.data.data;
  },

  // Finalizar reserva
  async completeReservation(id: number): Promise<Reservation> {
    const response = await httpClient.post<ApiResponse<Reservation>>(`/reservations/${id}/complete`);
    return response.data.data;
  },

  // Fazer checkout (finalizar sem exit_time manual)
  async checkoutReservation(id: number): Promise<Reservation> {
    const payload = {
      exit_time: new Date().toISOString(),
    };
    const response = await httpClient.post<ApiResponse<Reservation>>(`/reservations/${id}/complete`, payload);
    return response.data.data;
  },

  // Cancelar reserva
  async cancelReservation(id: number): Promise<void> {
    await httpClient.post(`/reservations/${id}/cancel`);
  },

  // Listar meus veículos
  async getMyVehicles(): Promise<Vehicle[]> {
    const response = await httpClient.get<ApiResponse<Vehicle[]>>('/vehicles');
    // Mapear 'type' do backend para 'vehicle_type' do frontend
    return response.data.data.map(v => ({
      ...v,
      vehicle_type: (v as any).type || v.vehicle_type
    } as Vehicle));
  },

  // Adicionar veículo
  async addVehicle(data: {
    license_plate: string;
    brand: string;
    model: string;
    color: string;
    vehicle_type: 'car' | 'motorcycle' | 'truck';
  }): Promise<Vehicle> {
    // Mapear vehicle_type para type (campo esperado pelo backend)
    const payload = {
      license_plate: data.license_plate,
      brand: data.brand,
      model: data.model,
      color: data.color,
      type: data.vehicle_type, // Renomear para 'type'
    };
    
    const response = await httpClient.post<ApiResponse<Vehicle>>('/vehicles', payload);
    // Mapear 'type' do backend para 'vehicle_type' do frontend
    const vehicle = response.data.data;
    return {
      ...vehicle,
      vehicle_type: (vehicle as any).type || vehicle.vehicle_type
    } as Vehicle;
  },

  // Remover veículo
  async removeVehicle(id: number): Promise<void> {
    await httpClient.delete(`/vehicles/${id}`);
  },
};

