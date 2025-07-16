export const formatDate = (date: Date, locale: string = 'id-ID'): string => {
  return date.toLocaleDateString(locale, {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

export const formatDateTime = (date: Date, locale: string = 'id-ID'): string => {
  return date.toLocaleString(locale, {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

export const formatTime = (date: Date, locale: string = 'id-ID'): string => {
  return date.toLocaleTimeString(locale, {
    hour: '2-digit',
    minute: '2-digit'
  });
};

export const addDays = (date: Date, days: number): Date => {
  const result = new Date(date);
  result.setDate(result.getDate() + days);
  return result;
};

export const addHours = (date: Date, hours: number): Date => {
  const result = new Date(date);
  result.setHours(result.getHours() + hours);
  return result;
};

export const isWeekend = (date: Date): boolean => {
  const day = date.getDay();
  return day === 0 || day === 6; // Sunday or Saturday
};

export const isBusinessDay = (date: Date): boolean => {
  return !isWeekend(date);
};

export const getBusinessDays = (startDate: Date, endDate: Date): number => {
  let count = 0;
  const current = new Date(startDate);
  
  while (current <= endDate) {
    if (isBusinessDay(current)) {
      count++;
    }
    current.setDate(current.getDate() + 1);
  }
  
  return count;
};

export const getNextBusinessDay = (date: Date): Date => {
  const next = new Date(date);
  next.setDate(next.getDate() + 1);
  
  while (isWeekend(next)) {
    next.setDate(next.getDate() + 1);
  }
  
  return next;
};

export const getDaysDifference = (startDate: Date, endDate: Date): number => {
  const timeDiff = endDate.getTime() - startDate.getTime();
  return Math.ceil(timeDiff / (1000 * 3600 * 24));
};

export const getHoursDifference = (startDate: Date, endDate: Date): number => {
  const timeDiff = endDate.getTime() - startDate.getTime();
  return Math.ceil(timeDiff / (1000 * 3600));
};

export const isTimeSlotAvailable = (
  startTime: Date,
  endTime: Date,
  existingBookings: Array<{ startTime: Date; endTime: Date }>
): boolean => {
  return !existingBookings.some(booking => 
    (startTime >= booking.startTime && startTime < booking.endTime) ||
    (endTime > booking.startTime && endTime <= booking.endTime) ||
    (startTime <= booking.startTime && endTime >= booking.endTime)
  );
};

export const generateTimeSlots = (
  startHour: number,
  endHour: number,
  intervalMinutes: number = 60
): string[] => {
  const slots: string[] = [];
  
  for (let hour = startHour; hour < endHour; hour++) {
    for (let minute = 0; minute < 60; minute += intervalMinutes) {
      const timeString = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
      slots.push(timeString);
    }
  }
  
  return slots;
};

export const isValidTimeRange = (startTime: Date, endTime: Date, minDurationHours: number = 1): boolean => {
  const durationHours = getHoursDifference(startTime, endTime);
  return durationHours >= minDurationHours;
};