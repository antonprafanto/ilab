import React, { useState, useEffect } from 'react';
import { Button } from '../../components/ui/Button';
import { Card } from '../../components/ui/Card';
import { Input } from '../../components/ui/Input';

interface Equipment {
  id: string;
  name: string;
  type: string;
  description?: string;
  status: 'available' | 'in_use' | 'maintenance' | 'out_of_order' | 'reserved';
  location: string;
  responsible_person: string;
  contact_info?: string;
  pricing: {
    hourlyRate?: number;
    setupFee?: number;
    additionalFees?: Record<string, number>;
  };
  booking_rules: {
    maxDurationHours?: number;
    minAdvanceHours?: number;
    requiresApproval?: boolean;
  };
  specifications?: Record<string, any>;
  images?: string[];
  category_name?: string;
  created_at: string;
  updated_at: string;
}

interface EquipmentResponse {
  equipment: Equipment[];
  pagination: {
    page: number;
    limit: number;
    total: number;
    totalPages: number;
    hasNext: boolean;
    hasPrev: boolean;
  };
}

const EquipmentPage: React.FC = () => {
  const [equipment, setEquipment] = useState<Equipment[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [pagination, setPagination] = useState({
    page: 1,
    limit: 10,
    total: 0,
    totalPages: 0,
    hasNext: false,
    hasPrev: false
  });

  // Filters
  const [search, setSearch] = useState('');
  const [typeFilter, setTypeFilter] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [locationFilter, setLocationFilter] = useState('');

  const [selectedEquipment, setSelectedEquipment] = useState<string[]>([]);
  const [showEquipmentModal, setShowEquipmentModal] = useState(false);
  const [editingEquipment, setEditingEquipment] = useState<Equipment | null>(null);
  const [equipmentForm, setEquipmentForm] = useState({
    name: '',
    type: 'gc_ms',
    description: '',
    location: '',
    responsiblePerson: '',
    contactInfo: '',
    pricing: {
      hourlyRate: 0,
      setupFee: 0
    },
    bookingRules: {
      maxDurationHours: 8,
      minAdvanceHours: 24,
      requiresApproval: true
    },
    specifications: {} as Record<string, any>
  });

  const equipmentTypes = [
    { value: 'gc_ms', label: 'GC-MS' },
    { value: 'lc_ms', label: 'LC-MS' },
    { value: 'aas', label: 'AAS' },
    { value: 'ftir', label: 'FTIR' },
    { value: 'pcr', label: 'PCR' },
    { value: 'freeze_dryer', label: 'Freeze Dryer' },
    { value: 'hplc', label: 'HPLC' },
    { value: 'spectrophotometer', label: 'Spectrophotometer' },
    { value: 'microscope', label: 'Microscope' },
    { value: 'centrifuge', label: 'Centrifuge' },
    { value: 'incubator', label: 'Incubator' },
    { value: 'other', label: 'Other' }
  ];

  const fetchEquipment = async () => {
    try {
      setLoading(true);
      const params = new URLSearchParams({
        page: pagination.page.toString(),
        limit: pagination.limit.toString(),
        ...(search && { search }),
        ...(typeFilter && { type: typeFilter }),
        ...(statusFilter && { status: statusFilter }),
        ...(locationFilter && { location: locationFilter })
      });

      const response = await fetch(`/api/equipment?${params}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('accessToken')}`,
          'Content-Type': 'application/json'
        }
      });

      if (!response.ok) {
        throw new Error('Failed to fetch equipment');
      }

      const data: { data: EquipmentResponse } = await response.json();
      setEquipment(data.data.equipment);
      setPagination(data.data.pagination);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Unknown error');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchEquipment();
  }, [pagination.page, search, typeFilter, statusFilter, locationFilter]);

  const handleStatusChange = async (equipmentId: string, newStatus: string) => {
    try {
      const response = await fetch(`/api/equipment/${equipmentId}`, {
        method: 'PUT',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('accessToken')}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ status: newStatus })
      });

      if (!response.ok) {
        throw new Error('Failed to update equipment status');
      }

      fetchEquipment();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Unknown error');
    }
  };

  const handleCreateEquipment = () => {
    setEditingEquipment(null);
    setEquipmentForm({
      name: '',
      type: 'gc_ms',
      description: '',
      location: '',
      responsiblePerson: '',
      contactInfo: '',
      pricing: {
        hourlyRate: 0,
        setupFee: 0
      },
      bookingRules: {
        maxDurationHours: 8,
        minAdvanceHours: 24,
        requiresApproval: true
      },
      specifications: {}
    });
    setShowEquipmentModal(true);
  };

  const handleEditEquipment = (equipment: Equipment) => {
    setEditingEquipment(equipment);
    setEquipmentForm({
      name: equipment.name,
      type: equipment.type,
      description: equipment.description || '',
      location: equipment.location,
      responsiblePerson: equipment.responsible_person,
      contactInfo: equipment.contact_info || '',
      pricing: equipment.pricing,
      bookingRules: equipment.booking_rules,
      specifications: equipment.specifications || {}
    });
    setShowEquipmentModal(true);
  };

  const handleSaveEquipment = async () => {
    try {
      const url = editingEquipment ? `/api/equipment/${editingEquipment.id}` : '/api/equipment';
      const method = editingEquipment ? 'PUT' : 'POST';

      const response = await fetch(url, {
        method,
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('accessToken')}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(equipmentForm)
      });

      if (!response.ok) {
        throw new Error('Failed to save equipment');
      }

      setShowEquipmentModal(false);
      setEditingEquipment(null);
      fetchEquipment();
    } catch (err) {
      setError(err instanceof Error ? err.message : 'Unknown error');
    }
  };

  const getStatusBadgeClass = (status: string) => {
    switch (status) {
      case 'available':
        return 'bg-green-100 text-green-800';
      case 'in_use':
        return 'bg-blue-100 text-blue-800';
      case 'maintenance':
        return 'bg-yellow-100 text-yellow-800';
      case 'out_of_order':
        return 'bg-red-100 text-red-800';
      case 'reserved':
        return 'bg-purple-100 text-purple-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  return (
    <div className="p-6">
      <div className="mb-6">
        <h1 className="text-2xl font-bold text-gray-900 mb-2">Equipment Management</h1>
        <p className="text-gray-600">Manage laboratory equipment and instruments</p>
      </div>

      {/* Filters and Search */}
      <Card className="mb-6">
        <div className="p-4">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Search
              </label>
              <Input
                type="text"
                placeholder="Search equipment..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="w-full"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Type
              </label>
              <select
                value={typeFilter}
                onChange={(e) => setTypeFilter(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">All Types</option>
                {equipmentTypes.map(type => (
                  <option key={type.value} value={type.value}>{type.label}</option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Status
              </label>
              <select
                value={statusFilter}
                onChange={(e) => setStatusFilter(e.target.value)}
                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">All Status</option>
                <option value="available">Available</option>
                <option value="in_use">In Use</option>
                <option value="maintenance">Maintenance</option>
                <option value="out_of_order">Out of Order</option>
                <option value="reserved">Reserved</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">
                Location
              </label>
              <Input
                type="text"
                placeholder="Filter by location..."
                value={locationFilter}
                onChange={(e) => setLocationFilter(e.target.value)}
                className="w-full"
              />
            </div>
          </div>
        </div>
      </Card>

      {/* Equipment Table */}
      <Card>
        <div className="p-4 border-b border-gray-200">
          <div className="flex justify-between items-center">
            <h2 className="text-lg font-semibold">Equipment List</h2>
            <div className="flex space-x-2">
              {selectedEquipment.length > 0 && (
                <div className="flex space-x-2">
                  <Button
                    variant="outline"
                    onClick={() => setSelectedEquipment([])}
                  >
                    Clear Selection ({selectedEquipment.length})
                  </Button>
                </div>
              )}
              <Button onClick={handleCreateEquipment}>
                Add Equipment
              </Button>
            </div>
          </div>
        </div>

        <div className="overflow-x-auto">
          {loading ? (
            <div className="p-8 text-center">
              <div className="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
              <p className="mt-2 text-gray-600">Loading equipment...</p>
            </div>
          ) : error ? (
            <div className="p-8 text-center text-red-600">
              <p>Error: {error}</p>
              <Button
                onClick={fetchEquipment}
                className="mt-2"
                variant="outline"
              >
                Retry
              </Button>
            </div>
          ) : (
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <input
                      type="checkbox"
                      checked={selectedEquipment.length === equipment.length && equipment.length > 0}
                      onChange={(e) => {
                        if (e.target.checked) {
                          setSelectedEquipment(equipment.map(eq => eq.id));
                        } else {
                          setSelectedEquipment([]);
                        }
                      }}
                      className="rounded border-gray-300"
                    />
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Equipment
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Type
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Location
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Responsible Person
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Pricing
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {equipment.map((eq) => (
                  <tr key={eq.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <input
                        type="checkbox"
                        checked={selectedEquipment.includes(eq.id)}
                        onChange={(e) => {
                          if (e.target.checked) {
                            setSelectedEquipment([...selectedEquipment, eq.id]);
                          } else {
                            setSelectedEquipment(selectedEquipment.filter(id => id !== eq.id));
                          }
                        }}
                        className="rounded border-gray-300"
                      />
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div>
                        <div className="text-sm font-medium text-gray-900">
                          {eq.name}
                        </div>
                        {eq.description && (
                          <div className="text-sm text-gray-500 truncate max-w-xs">
                            {eq.description}
                          </div>
                        )}
                        {eq.category_name && (
                          <div className="text-xs text-gray-400">{eq.category_name}</div>
                        )}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {equipmentTypes.find(t => t.value === eq.type)?.label || eq.type}
                      </span>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <select
                        value={eq.status}
                        onChange={(e) => handleStatusChange(eq.id, e.target.value)}
                        className={`text-xs font-medium px-2.5 py-0.5 rounded-full border-0 ${getStatusBadgeClass(eq.status)}`}
                      >
                        <option value="available">Available</option>
                        <option value="in_use">In Use</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="out_of_order">Out of Order</option>
                        <option value="reserved">Reserved</option>
                      </select>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {eq.location}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm text-gray-900">{eq.responsible_person}</div>
                      {eq.contact_info && (
                        <div className="text-sm text-gray-500">{eq.contact_info}</div>
                      )}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {eq.pricing.hourlyRate && (
                        <div>Rp {eq.pricing.hourlyRate.toLocaleString()}/hour</div>
                      )}
                      {eq.pricing.setupFee && (
                        <div className="text-xs text-gray-500">
                          Setup: Rp {eq.pricing.setupFee.toLocaleString()}
                        </div>
                      )}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <div className="flex space-x-2">
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleEditEquipment(eq)}
                        >
                          Edit
                        </Button>
                        <Button
                          variant="destructive"
                          size="sm"
                          onClick={() => {
                            if (confirm('Are you sure you want to delete this equipment?')) {
                              // Handle delete
                            }
                          }}
                        >
                          Delete
                        </Button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>

        {/* Pagination */}
        {pagination.totalPages > 1 && (
          <div className="px-6 py-3 border-t border-gray-200">
            <div className="flex items-center justify-between">
              <div className="text-sm text-gray-700">
                Showing {((pagination.page - 1) * pagination.limit) + 1} to{' '}
                {Math.min(pagination.page * pagination.limit, pagination.total)} of{' '}
                {pagination.total} results
              </div>
              <div className="flex space-x-2">
                <Button
                  variant="outline"
                  size="sm"
                  disabled={!pagination.hasPrev}
                  onClick={() => setPagination(prev => ({ ...prev, page: prev.page - 1 }))}
                >
                  Previous
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  disabled={!pagination.hasNext}
                  onClick={() => setPagination(prev => ({ ...prev, page: prev.page + 1 }))}
                >
                  Next
                </Button>
              </div>
            </div>
          </div>
        )}
      </Card>

      {/* Equipment Modal */}
      {showEquipmentModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <h3 className="text-lg font-medium mb-4">
              {editingEquipment ? 'Edit Equipment' : 'Add New Equipment'}
            </h3>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {/* Basic Info */}
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Equipment Name
                  </label>
                  <Input
                    type="text"
                    value={equipmentForm.name}
                    onChange={(e) => setEquipmentForm(prev => ({ ...prev, name: e.target.value }))}
                    placeholder="e.g., GC-MS Agilent 7890A"
                    className="w-full"
                    required
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Type
                  </label>
                  <select
                    value={equipmentForm.type}
                    onChange={(e) => setEquipmentForm(prev => ({ ...prev, type: e.target.value }))}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                  >
                    {equipmentTypes.map(type => (
                      <option key={type.value} value={type.value}>{type.label}</option>
                    ))}
                  </select>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Description
                  </label>
                  <textarea
                    value={equipmentForm.description}
                    onChange={(e) => setEquipmentForm(prev => ({ ...prev, description: e.target.value }))}
                    placeholder="Equipment description..."
                    rows={3}
                    className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Location
                  </label>
                  <Input
                    type="text"
                    value={equipmentForm.location}
                    onChange={(e) => setEquipmentForm(prev => ({ ...prev, location: e.target.value }))}
                    placeholder="e.g., Lab Room 101"
                    className="w-full"
                    required
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Responsible Person
                  </label>
                  <Input
                    type="text"
                    value={equipmentForm.responsiblePerson}
                    onChange={(e) => setEquipmentForm(prev => ({ ...prev, responsiblePerson: e.target.value }))}
                    placeholder="e.g., Dr. John Doe"
                    className="w-full"
                    required
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">
                    Contact Info
                  </label>
                  <Input
                    type="text"
                    value={equipmentForm.contactInfo}
                    onChange={(e) => setEquipmentForm(prev => ({ ...prev, contactInfo: e.target.value }))}
                    placeholder="Phone/Email"
                    className="w-full"
                  />
                </div>
              </div>

              {/* Pricing and Rules */}
              <div className="space-y-4">
                <div>
                  <h4 className="text-sm font-medium text-gray-700 mb-3">Pricing</h4>
                  <div className="space-y-3">
                    <div>
                      <label className="block text-xs text-gray-600 mb-1">
                        Hourly Rate (Rp)
                      </label>
                      <Input
                        type="number"
                        min="0"
                        value={equipmentForm.pricing.hourlyRate}
                        onChange={(e) => setEquipmentForm(prev => ({
                          ...prev,
                          pricing: { ...prev.pricing, hourlyRate: parseFloat(e.target.value) || 0 }
                        }))}
                        className="w-full"
                      />
                    </div>
                    <div>
                      <label className="block text-xs text-gray-600 mb-1">
                        Setup Fee (Rp)
                      </label>
                      <Input
                        type="number"
                        min="0"
                        value={equipmentForm.pricing.setupFee}
                        onChange={(e) => setEquipmentForm(prev => ({
                          ...prev,
                          pricing: { ...prev.pricing, setupFee: parseFloat(e.target.value) || 0 }
                        }))}
                        className="w-full"
                      />
                    </div>
                  </div>
                </div>

                <div>
                  <h4 className="text-sm font-medium text-gray-700 mb-3">Booking Rules</h4>
                  <div className="space-y-3">
                    <div>
                      <label className="block text-xs text-gray-600 mb-1">
                        Max Duration (Hours)
                      </label>
                      <Input
                        type="number"
                        min="1"
                        value={equipmentForm.bookingRules.maxDurationHours}
                        onChange={(e) => setEquipmentForm(prev => ({
                          ...prev,
                          bookingRules: { ...prev.bookingRules, maxDurationHours: parseInt(e.target.value) || 8 }
                        }))}
                        className="w-full"
                      />
                    </div>
                    <div>
                      <label className="block text-xs text-gray-600 mb-1">
                        Min Advance Notice (Hours)
                      </label>
                      <Input
                        type="number"
                        min="0"
                        value={equipmentForm.bookingRules.minAdvanceHours}
                        onChange={(e) => setEquipmentForm(prev => ({
                          ...prev,
                          bookingRules: { ...prev.bookingRules, minAdvanceHours: parseInt(e.target.value) || 24 }
                        }))}
                        className="w-full"
                      />
                    </div>
                    <div className="flex items-center">
                      <input
                        type="checkbox"
                        id="requiresApproval"
                        checked={equipmentForm.bookingRules.requiresApproval}
                        onChange={(e) => setEquipmentForm(prev => ({
                          ...prev,
                          bookingRules: { ...prev.bookingRules, requiresApproval: e.target.checked }
                        }))}
                        className="rounded border-gray-300"
                      />
                      <label htmlFor="requiresApproval" className="ml-2 text-xs text-gray-700">
                        Requires Approval
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div className="flex justify-end space-x-2 mt-6">
              <Button
                variant="outline"
                onClick={() => {
                  setShowEquipmentModal(false);
                  setEditingEquipment(null);
                }}
              >
                Cancel
              </Button>
              <Button onClick={handleSaveEquipment}>
                {editingEquipment ? 'Update' : 'Create'}
              </Button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default EquipmentPage;