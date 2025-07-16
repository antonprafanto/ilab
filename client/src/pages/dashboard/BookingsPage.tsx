import React, { useState, useEffect } from 'react';
import { Button } from '../../components/ui/Button';
import { Card } from '../../components/ui/Card';
import { Input } from '../../components/ui/Input';

interface Equipment {
  id: string;
  name: string;
  type: string;
  location: string;
  status: string;
  pricing: {
    hourlyRate?: number;
    setupFee?: number;
  };
  booking_rules: {
    maxDurationHours?: number;
    minAdvanceHours?: number;
  };
}

interface Booking {
  id: string;
  title: string;
  description?: string;
  start_time: string;
  end_time: string;
  status: 'pending' | 'confirmed' | 'in_progress' | 'completed' | 'cancelled' | 'no_show';
  priority: 'low' | 'normal' | 'high' | 'urgent';
  equipment_id: string;
  equipment_name: string;
  equipment_type: string;
  user_name: string;
  user_email: string;
  estimated_cost: number;
  created_at: string;
}

interface BookingsResponse {
  bookings: Booking[];
  pagination: {
    page: number;
    limit: number;
    total: number;
    totalPages: number;
    hasNext: boolean;
    hasPrev: boolean;
  };
}

const BookingsPage: React.FC = () => {
  const [bookings, setBookings] = useState<Booking[]>([]);
  const [equipment, setEquipment] = useState<Equipment[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  
  // Calendar state
  const [currentDate, setCurrentDate] = useState(new Date());
  const [selectedDate, setSelectedDate] = useState<Date | null>(null);
  const [selectedEquipment, setSelectedEquipment] = useState<string>('');
  const [calendarView, setCalendarView] = useState<'month' | 'week' | 'day'>('week');
  
  // Modal state
  const [showBookingModal, setShowBookingModal] = useState(false);
  const [editingBooking, setEditingBooking] = useState<Booking | null>(null);
  const [bookingForm, setBookingForm] = useState({
    equipmentId: '',
    title: '',
    description: '',
    startTime: '',
    endTime: '',
    purpose: '',
    sampleType: '',
    numberOfSamples: 1,
    specialRequirements: ''
  });

  // Filters
  const [statusFilter, setStatusFilter] = useState('');
  const [equipmentFilter, setEquipmentFilter] = useState('');

  const fetchBookings = async () => {
    try {
      setLoading(true);
      const params = new URLSearchParams({
        limit: '100', // Get more for calendar view
        ...(statusFilter && { status: statusFilter }),
        ...(equipmentFilter && { equipmentId: equipmentFilter }),
        startDate: getWeekStart(currentDate).toISOString().split('T')[0],
        endDate: getWeekEnd(currentDate).toISOString().split('T')[0]
      });

      const response = await fetch(`/api/bookings?${params}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('accessToken')}`,
          'Content-Type': 'application/json'
        }
      });

      if (!response.ok) {
        throw new Error('Failed to fetch bookings');
      }

      const data: { data: BookingsResponse } = await response.json();
      setBookings(data.data.bookings);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Unknown error');
    } finally {
      setLoading(false);
    }
  };

  const fetchEquipment = async () => {
    try {
      const response = await fetch('/api/equipment?limit=100', {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('accessToken')}`,
          'Content-Type': 'application/json'
        }
      });

      if (!response.ok) {
        throw new Error('Failed to fetch equipment');
      }

      const data = await response.json();
      setEquipment(data.data.equipment);
    } catch (err) {
      console.error('Error fetching equipment:', err);
    }
  };

  useEffect(() => {
    fetchBookings();
    fetchEquipment();
  }, [currentDate, statusFilter, equipmentFilter]);

  const getWeekStart = (date: Date) => {
    const start = new Date(date);
    const day = start.getDay();
    const diff = start.getDate() - day + (day === 0 ? -6 : 1); // Monday as first day
    return new Date(start.setDate(diff));
  };

  const getWeekEnd = (date: Date) => {
    const end = getWeekStart(date);
    end.setDate(end.getDate() + 6);
    return end;
  };

  const getWeekDays = () => {
    const start = getWeekStart(currentDate);
    const days = [];
    for (let i = 0; i < 7; i++) {
      const day = new Date(start);
      day.setDate(start.getDate() + i);
      days.push(day);
    }
    return days;
  };

  const getBookingsForDate = (date: Date) => {
    return bookings.filter(booking => {
      const bookingDate = new Date(booking.start_time);
      return bookingDate.toDateString() === date.toDateString();
    });
  };

  const getTimeSlots = () => {
    const slots = [];
    for (let hour = 7; hour <= 22; hour++) {
      slots.push(`${hour.toString().padStart(2, '0')}:00`);
    }
    return slots;
  };

  const handleCreateBooking = (date?: Date, hour?: string) => {
    setEditingBooking(null);
    
    let startTime = '';
    let endTime = '';
    
    if (date && hour) {
      const start = new Date(date);
      const [h] = hour.split(':');
      start.setHours(parseInt(h), 0, 0, 0);
      const end = new Date(start);
      end.setHours(start.getHours() + 1);
      
      startTime = start.toISOString().slice(0, 16);
      endTime = end.toISOString().slice(0, 16);
    }
    
    setBookingForm({
      equipmentId: selectedEquipment || '',
      title: '',
      description: '',
      startTime,
      endTime,
      purpose: '',
      sampleType: '',
      numberOfSamples: 1,
      specialRequirements: ''
    });
    setShowBookingModal(true);
  };

  const handleSaveBooking = async () => {
    try {
      const url = editingBooking ? `/api/bookings/${editingBooking.id}` : '/api/bookings';
      const method = editingBooking ? 'PUT' : 'POST';

      const response = await fetch(url, {
        method,
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('accessToken')}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(bookingForm)
      });

      if (!response.ok) {
        throw new Error('Failed to save booking');
      }

      setShowBookingModal(false);
      setEditingBooking(null);
      fetchBookings();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Unknown error');
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'confirmed':
        return 'bg-green-100 text-green-800 border-green-200';
      case 'pending':
        return 'bg-yellow-100 text-yellow-800 border-yellow-200';
      case 'in_progress':
        return 'bg-blue-100 text-blue-800 border-blue-200';
      case 'completed':
        return 'bg-gray-100 text-gray-800 border-gray-200';
      case 'cancelled':
        return 'bg-red-100 text-red-800 border-red-200';
      default:
        return 'bg-gray-100 text-gray-800 border-gray-200';
    }
  };

  const formatTime = (timeString: string) => {
    return new Date(timeString).toLocaleTimeString('id-ID', {
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const weekDays = getWeekDays();
  const timeSlots = getTimeSlots();

  return (
    <div className="p-6">
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-900 mb-2">Booking Management</h1>
        <p className="text-gray-600">Manage equipment bookings and reservations</p>
      </div>

      {/* Controls */}
      <Card className="mb-6">
        <div className="p-4">
          <div className="flex flex-wrap items-center justify-between gap-4">
            <div className="flex items-center space-x-4">
              <Button
                variant="outline"
                onClick={() => {
                  const prev = new Date(currentDate);
                  prev.setDate(prev.getDate() - 7);
                  setCurrentDate(prev);
                }}
              >
                ← Previous Week
              </Button>
              <div className="text-lg font-medium">
                {weekDays[0].toLocaleDateString('id-ID', { 
                  day: 'numeric', 
                  month: 'long', 
                  year: 'numeric' 
                })} - {weekDays[6].toLocaleDateString('id-ID', { 
                  day: 'numeric', 
                  month: 'long', 
                  year: 'numeric' 
                })}
              </div>
              <Button
                variant="outline"
                onClick={() => {
                  const next = new Date(currentDate);
                  next.setDate(next.getDate() + 7);
                  setCurrentDate(next);
                }}
              >
                Next Week →
              </Button>
            </div>

            <div className="flex items-center space-x-4">
              <select
                value={equipmentFilter}
                onChange={(e) => setEquipmentFilter(e.target.value)}
                className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">All Equipment</option>
                {equipment.map(eq => (
                  <option key={eq.id} value={eq.id}>{eq.name}</option>
                ))}
              </select>

              <select
                value={statusFilter}
                onChange={(e) => setStatusFilter(e.target.value)}
                className="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>

              <Button onClick={() => handleCreateBooking()}>
                New Booking
              </Button>
            </div>
          </div>
        </div>
      </Card>

      {/* Calendar View */}
      <Card>
        <div className="overflow-x-auto">
          {loading ? (
            <div className="p-8 text-center">
              <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              <p className="mt-2 text-gray-600">Loading bookings...</p>
            </div>
          ) : error ? (
            <div className="p-8 text-center text-red-600">
              <p>Error: {error}</p>
              <Button
                onClick={fetchBookings}
                className="mt-2"
                variant="outline"
              >
                Retry
              </Button>
            </div>
          ) : (
            <div className="min-w-full">
              {/* Calendar Header */}
              <div className="grid grid-cols-8 border-b border-gray-200 bg-gray-50">
                <div className="p-3 text-sm font-medium text-gray-500">Time</div>
                {weekDays.map(day => (
                  <div key={day.toISOString()} className="p-3 text-center">
                    <div className="text-sm font-medium text-gray-900">
                      {day.toLocaleDateString('id-ID', { weekday: 'short' })}
                    </div>
                    <div className="text-lg font-bold text-gray-700">
                      {day.getDate()}
                    </div>
                  </div>
                ))}
              </div>

              {/* Calendar Body */}
              <div className="grid grid-cols-8">
                {timeSlots.map(time => (
                  <React.Fragment key={time}>
                    <div className="p-3 border-b border-gray-100 bg-gray-50 text-sm text-gray-600 font-medium">
                      {time}
                    </div>
                    {weekDays.map(day => {
                      const dayBookings = getBookingsForDate(day);
                      const hourBookings = dayBookings.filter(booking => {
                        const bookingHour = new Date(booking.start_time).getHours();
                        const slotHour = parseInt(time.split(':')[0]);
                        return bookingHour === slotHour;
                      });

                      return (
                        <div
                          key={`${day.toISOString()}-${time}`}
                          className="p-1 border-b border-gray-100 min-h-[60px] hover:bg-gray-50 cursor-pointer relative"
                          onClick={() => handleCreateBooking(day, time)}
                        >
                          {hourBookings.map(booking => (
                            <div
                              key={booking.id}
                              className={`p-1 mb-1 rounded text-xs border ${getStatusColor(booking.status)}`}
                              onClick={(e) => {
                                e.stopPropagation();
                                // Handle booking click
                              }}
                            >
                              <div className="font-medium truncate">{booking.title}</div>
                              <div className="text-xs opacity-75">
                                {formatTime(booking.start_time)} - {formatTime(booking.end_time)}
                              </div>
                              <div className="text-xs opacity-75 truncate">
                                {booking.equipment_name}
                              </div>
                            </div>
                          ))}
                        </div>
                      );
                    })}
                  </React.Fragment>
                ))}
              </div>
            </div>
          )}
        </div>
      </Card>

      {/* Booking Modal */}
      {showBookingModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <h3 className="text-lg font-medium mb-4">
              {editingBooking ? 'Edit Booking' : 'Create New Booking'}
            </h3>

            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Equipment
                </label>
                <select
                  value={bookingForm.equipmentId}
                  onChange={(e) => setBookingForm(prev => ({ ...prev, equipmentId: e.target.value }))}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                >
                  <option value="">Select equipment...</option>
                  {equipment.filter(eq => eq.status === 'available').map(eq => (
                    <option key={eq.id} value={eq.id}>
                      {eq.name} - {eq.location}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Title
                </label>
                <Input
                  type="text"
                  value={bookingForm.title}
                  onChange={(e) => setBookingForm(prev => ({ ...prev, title: e.target.value }))}
                  placeholder="Booking title..."
                  className="w-full"
                  required
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Start Time
                  </label>
                  <Input
                    type="datetime-local"
                    value={bookingForm.startTime}
                    onChange={(e) => setBookingForm(prev => ({ ...prev, startTime: e.target.value }))}
                    className="w-full"
                    required
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    End Time
                  </label>
                  <Input
                    type="datetime-local"
                    value={bookingForm.endTime}
                    onChange={(e) => setBookingForm(prev => ({ ...prev, endTime: e.target.value }))}
                    className="w-full"
                    required
                  />
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Purpose
                </label>
                <textarea
                  value={bookingForm.purpose}
                  onChange={(e) => setBookingForm(prev => ({ ...prev, purpose: e.target.value }))}
                  placeholder="Research purpose..."
                  rows={3}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Sample Type
                  </label>
                  <Input
                    type="text"
                    value={bookingForm.sampleType}
                    onChange={(e) => setBookingForm(prev => ({ ...prev, sampleType: e.target.value }))}
                    placeholder="e.g., Water, Soil, Food..."
                    className="w-full"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Number of Samples
                  </label>
                  <Input
                    type="number"
                    min="1"
                    value={bookingForm.numberOfSamples}
                    onChange={(e) => setBookingForm(prev => ({ ...prev, numberOfSamples: parseInt(e.target.value) }))}
                    className="w-full"
                  />
                </div>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Special Requirements
                </label>
                <textarea
                  value={bookingForm.specialRequirements}
                  onChange={(e) => setBookingForm(prev => ({ ...prev, specialRequirements: e.target.value }))}
                  placeholder="Any special requirements or notes..."
                  rows={2}
                  className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <div className="flex justify-end space-x-2 mt-6">
              <Button
                variant="outline"
                onClick={() => {
                  setShowBookingModal(false);
                  setEditingBooking(null);
                }}
              >
                Cancel
              </Button>
              <Button onClick={handleSaveBooking}>
                {editingBooking ? 'Update' : 'Create'}
              </Button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default BookingsPage;