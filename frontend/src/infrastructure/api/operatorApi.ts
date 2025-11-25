import { httpClient } from './httpClient';
import type { ApiResponse, ParkingSpot, Reservation } from '../../domain/types';

export const operatorApi = {
  // Estatísticas do dashboard
  async getStats(): Promise<{
    totalSpots: number;
    availableSpots: number;
    activeReservations: number;
    todayRevenue: number;
  }> {
    const response = await httpClient.get<ApiResponse<{
      total_spots: number;
      available_spots: number;
      active_reservations: number;
      today_revenue: number;
    }>>('/operators/stats');
    return {
      totalSpots: response.data.data.total_spots,
      availableSpots: response.data.data.available_spots,
      activeReservations: response.data.data.active_reservations,
      todayRevenue: response.data.data.today_revenue,
    };
  },

  // Listar todas as vagas
  async getAllSpots(): Promise<ParkingSpot[]> {
    const response = await httpClient.get<ApiResponse<ParkingSpot[]>>('/parking-spots');
    return response.data.data;
  },

  // Criar vaga
  async createSpot(data: {
    number: string;
    type: 'regular' | 'vip' | 'disabled';
    hourly_price: number;
    width: number;
    length: number;
  }): Promise<ParkingSpot> {
    const response = await httpClient.post<ApiResponse<ParkingSpot>>('/parking-spots', data);
    return response.data.data;
  },

  // Atualizar vaga
  async updateSpot(id: number, data: Partial<ParkingSpot>): Promise<ParkingSpot> {
    const response = await httpClient.put<ApiResponse<ParkingSpot>>(`/parking-spots/${id}`, data);
    return response.data.data;
  },

  // Deletar vaga
  async deleteSpot(id: number): Promise<void> {
    await httpClient.delete(`/parking-spots/${id}`);
  },

  // Listar todas as reservas
  async getAllReservations(filters?: {
    status?: 'active' | 'completed' | 'cancelled';
    date?: string;
  }): Promise<Reservation[]> {
    const params = new URLSearchParams();
    if (filters?.status) params.append('status', filters.status);
    if (filters?.date) params.append('date', filters.date);
    
    const response = await httpClient.get<ApiResponse<Reservation[]>>(
      `/reservations${params.toString() ? `?${params.toString()}` : ''}`
    );
    return response.data.data;
  },

  // Buscar por placa
  async searchByPlate(plate: string): Promise<Reservation[]> {
    const response = await httpClient.get<ApiResponse<Reservation[]>>(
      `/reservations/search?plate=${plate}`
    );
    return response.data.data;
  },

  // Buscar reserva ativa por vaga
  async getActiveReservationBySpot(spotId: number): Promise<Reservation | null> {
    try {
      const response = await httpClient.get<ApiResponse<Reservation>>(
        `/reservations/active-by-spot/${spotId}`
      );
      return response.data.data;
    } catch (error: any) {
      // Se retornar 404, não há reserva ativa
      if (error?.response?.status === 404) {
        return null;
      }
      throw error;
    }
  },

  // Finalizar reserva como operador (com observações)
  async finalizeReservation(reservationId: number, notes?: string): Promise<Reservation> {
    const response = await httpClient.post<ApiResponse<Reservation>>(
      `/reservations/${reservationId}/operator-finalize`,
      {
        exit_time: new Date().toISOString(),
        operator_notes: notes,
      }
    );
    return response.data.data;
  },
};
