export type UserRole = 'Admin' | 'Survivor';

export interface User {
  id: string;
  name: string;
  email: string;
  phone: string;
  basecamp: string;
  role: UserRole;
  avatarUrl?: string;
  password?: string;
}

export interface Category {
  id: string;
  name: string;
}

export type UnitStatus = 'Tersedia' | 'Dipinjam';

export interface Unit {
  id: string;
  code: string;
  name: string;
  description: string;
  categoryIds: string[];
  status: UnitStatus;
  imageUrl?: string;
}

export type LoanStatus = 'Aktif' | 'Terlambat' | 'Selesai';

export interface Loan {
  id: string;
  survivorId: string;
  survivorName: string;
  survivorEmail: string;
  unitId: string;
  unitCode: string;
  unitName: string;
  loanDate: string; // YYYY-MM-DD
  dueDate: string;  // YYYY-MM-DD (loanDate + 5 days)
  returnDate?: string; // YYYY-MM-DD
  overdueDays: number;
  fineAmount: number;
  status: LoanStatus;
}

export interface ToastMessage {
  id: string;
  type: 'success' | 'error' | 'warning' | 'info';
  message: string;
}

export type PageRoute = 
  | 'login'
  | 'register'
  | 'admin-dashboard'
  | 'admin-categories'
  | 'admin-units'
  | 'admin-members'
  | 'admin-loans'
  | 'admin-history'
  | 'survivor-dashboard'
  | 'survivor-catalog'
  | 'survivor-loans'
  | 'profile';
