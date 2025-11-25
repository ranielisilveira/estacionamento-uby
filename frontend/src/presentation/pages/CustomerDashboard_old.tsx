import { useState, useEffect } from 'react';
import { useAuthStore } from '../../application/stores/authStore';
import { authApi } from '../../infrastructure/api/authApi';
import { parkingApi } from '../../infrastructure/api/parkingApi';
import type { ParkingSpot, Reservation, Vehicle, Customer } from '../../domain/types';

export function CustomerDashboard() {
  const { user, clearAuth } = useAuthStore();
  const customer = user as Customer;
  
  const [availableSpots, setAvailableSpots] = useState<ParkingSpot[]>([]);
  const [myReservations, setMyReservations] = useState<Reservation[]>([]);
  const [myVehicles, setMyVehicles] = useState<Vehicle[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setIsLoading(true);
      const [spots, reservations, vehicles] = await Promise.all([
        parkingApi.getAvailableSpots(),
        parkingApi.getMyReservations(),
        parkingApi.getMyVehicles(),
      ]);
      
      setAvailableSpots(spots);
      setMyReservations(reservations);
      setMyVehicles(vehicles);
    } catch (err) {
      setError('Erro ao carregar dados');
    } finally {
      setIsLoading(false);
    }
  };

  const handleLogout = async () => {
    try {
      await authApi.logout();
      clearAuth();
      window.location.href = '/login';
    } catch (err) {
      console.error('Erro ao fazer logout:', err);
    }
  };

  const handleReserve = async (spotId: number) => {
    if (myVehicles.length === 0) {
      alert('Você precisa cadastrar um veículo primeiro!');
      return;
    }

    try {
      await parkingApi.createReservation({
        parking_spot_id: spotId,
        vehicle_id: myVehicles[0].id,
      });
      
      alert('Reserva criada com sucesso!');
      loadData();
    } catch (err) {
      alert('Erro ao criar reserva');
    }
  };

  const handleCancelReservation = async (id: number) => {
    if (!confirm('Deseja realmente cancelar esta reserva?')) return;

    try {
      await parkingApi.cancelReservation(id);
      alert('Reserva cancelada!');
      loadData();
    } catch (err) {
      alert('Erro ao cancelar reserva');
    }
  };

  if (isLoading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto mb-4"></div>
          <p className="text-gray-600">Carregando...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow-sm border-b border-gray-200">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
          <div className="flex justify-between items-center">
            <div>
              <h1 className="text-2xl font-bold text-gray-900">Estacionamento Uby</h1>
              <p className="text-sm text-gray-600">Bem-vindo, {customer?.name}!</p>
            </div>
            <button
              onClick={handleLogout}
              className="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
            >
              Sair
            </button>
          </div>
        </div>
      </header>

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {error && (
          <div className="bg-red-50 border-2 border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
            {error}
          </div>
        )}

        {/* Minhas Reservas */}
        <section className="mb-8">
          <h2 className="text-xl font-bold text-gray-900 mb-4">Minhas Reservas</h2>
          
          {myReservations.length === 0 ? (
            <div className="card text-center py-8">
              <p className="text-gray-600">Você ainda não tem reservas</p>
            </div>
          ) : (
            <div className="grid gap-4">
              {myReservations.map((reservation) => (
                <div key={reservation.id} className="card">
                  <div className="flex justify-between items-start">
                    <div>
                      <h3 className="font-semibold text-gray-900">
                        Vaga {reservation.parking_spot?.spot_number}
                      </h3>
                      <p className="text-sm text-gray-600">
                        Entrada: {new Date(reservation.entry_time).toLocaleString('pt-BR')}
                      </p>
                      {reservation.exit_time && (
                        <p className="text-sm text-gray-600">
                          Saída: {new Date(reservation.exit_time).toLocaleString('pt-BR')}
                        </p>
                      )}
                      <p className="text-sm text-gray-600">
                        Veículo: {reservation.vehicle?.license_plate}
                      </p>
                      <span className={`inline-block mt-2 px-3 py-1 rounded-full text-xs font-medium ${
                        reservation.status === 'active' ? 'bg-green-100 text-green-800' :
                        reservation.status === 'completed' ? 'bg-blue-100 text-blue-800' :
                        'bg-gray-100 text-gray-800'
                      }`}>
                        {reservation.status === 'active' ? 'Ativa' :
                         reservation.status === 'completed' ? 'Concluída' : 'Cancelada'}
                      </span>
                    </div>
                    
                    <div className="text-right">
                      {reservation.total_price && (
                        <p className="text-lg font-bold text-primary-600">
                          R$ {reservation.total_price.toFixed(2)}
                        </p>
                      )}
                      {reservation.status === 'active' && (
                        <button
                          onClick={() => handleCancelReservation(reservation.id)}
                          className="mt-2 text-sm text-red-600 hover:text-red-800"
                        >
                          Cancelar
                        </button>
                      )}
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </section>

        {/* Vagas Disponíveis */}
        <section>
          <h2 className="text-xl font-bold text-gray-900 mb-4">Vagas Disponíveis</h2>
          
          {availableSpots.length === 0 ? (
            <div className="card text-center py-8">
              <p className="text-gray-600">Não há vagas disponíveis no momento</p>
            </div>
          ) : (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {availableSpots.map((spot) => (
                <div key={spot.id} className="card hover:shadow-lg transition-shadow">
                  <div className="flex justify-between items-start mb-3">
                    <h3 className="text-lg font-semibold text-gray-900">
                      Vaga {spot.spot_number}
                    </h3>
                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                      spot.spot_type === 'vip' ? 'bg-yellow-100 text-yellow-800' :
                      spot.spot_type === 'handicapped' ? 'bg-blue-100 text-blue-800' :
                      'bg-gray-100 text-gray-800'
                    }`}>
                      {spot.spot_type === 'vip' ? 'VIP' :
                       spot.spot_type === 'handicapped' ? 'PCD' : 'Regular'}
                    </span>
                  </div>
                  
                  <div className="space-y-2 text-sm text-gray-600 mb-4">
                    <p>Andar: {spot.floor}</p>
                    <p>Seção: {spot.section}</p>
                    <p>Dimensões: {spot.width}m × {spot.length}m</p>
                    <p className="text-lg font-bold text-primary-600">
                      R$ {spot.hourly_rate.toFixed(2)}/hora
                    </p>
                  </div>
                  
                  <button
                    onClick={() => handleReserve(spot.id)}
                    className="btn-primary w-full"
                  >
                    Reservar
                  </button>
                </div>
              ))}
            </div>
          )}
        </section>
      </main>
    </div>
  );
}
