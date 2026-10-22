import React, { createContext, useContext, useState, useEffect } from 'react';
import type { Category, Loan, PageRoute, ToastMessage, Unit, User, UserRole } from '../types';
import {
  DAILY_FINE_RATE,
  INITIAL_CATEGORIES,
  INITIAL_LOANS,
  INITIAL_UNITS,
  INITIAL_USERS,
  LOAN_DURATION_DAYS,
  MAX_ACTIVE_LOANS,
} from '../mock/data';

interface DeadlockContextType {
  currentUser: User | null;
  users: User[];
  categories: Category[];
  units: Unit[];
  loans: Loan[];
  currentPage: PageRoute;
  toasts: ToastMessage[];
  stats: {
    totalUnits: number;
    availableUnits: number;
    borrowedUnits: number;
    overdueLoansCount: number;
  };
  setCurrentPage: (page: PageRoute) => void;
  login: (email: string, password: string) => { success: boolean; message?: string };
  register: (data: { name: string; email: string; phone: string; basecamp: string; password: string }) => { success: boolean; message?: string };
  logout: () => void;
  switchRoleDev: (role: UserRole, specificUserId?: string) => void;
  addCategory: (name: string) => { success: boolean; message?: string };
  updateCategory: (id: string, name: string) => { success: boolean; message?: string };
  deleteCategory: (id: string) => { success: boolean; message?: string };
  addUnit: (data: { code: string; name: string; description: string; categoryIds: string[]; imageUrl?: string }) => { success: boolean; message?: string };
  updateUnit: (id: string, data: { code: string; name: string; description: string; categoryIds: string[]; imageUrl?: string }) => { success: boolean; message?: string };
  deleteUnit: (id: string) => { success: boolean; message?: string };
  addMember: (data: { name: string; email: string; phone: string; basecamp: string; password?: string; role?: UserRole }) => { success: boolean; message?: string };
  updateMember: (id: string, data: Partial<User>) => { success: boolean; message?: string };
  deleteMember: (id: string) => { success: boolean; message?: string };
  borrowUnit: (unitId: string) => { success: boolean; message?: string };
  processReturn: (loanId: string, returnDateStr?: string) => { success: boolean; message?: string };
  updateProfile: (data: Partial<User>) => { success: boolean; message?: string };
  changePassword: (newPass: string) => { success: boolean; message?: string };
  showToast: (message: string, type?: 'success' | 'error' | 'warning' | 'info') => void;
  removeToast: (id: string) => void;
  getSurvivorActiveLoansCount: (survivorId: string) => number;
  resetAllData: () => void;
}

const DeadlockContext = createContext<DeadlockContextType | undefined>(undefined);

const STORAGE_KEY_PREFIX = 'deadlock_v1_';

export const DeadlockProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [users, setUsers] = useState<User[]>(() => {
    const saved = localStorage.getItem(STORAGE_KEY_PREFIX + 'users');
    return saved ? JSON.parse(saved) : INITIAL_USERS;
  });

  const [currentUser, setCurrentUser] = useState<User | null>(() => {
    const saved = localStorage.getItem(STORAGE_KEY_PREFIX + 'currentUser');
    if (saved) return JSON.parse(saved);
    // Default to admin for initial dashboard preview
    return INITIAL_USERS[0];
  });

  const [categories, setCategories] = useState<Category[]>(() => {
    const saved = localStorage.getItem(STORAGE_KEY_PREFIX + 'categories');
    return saved ? JSON.parse(saved) : INITIAL_CATEGORIES;
  });

  const [units, setUnits] = useState<Unit[]>(() => {
    const saved = localStorage.getItem(STORAGE_KEY_PREFIX + 'units');
    if (saved) {
      const parsed: Unit[] = JSON.parse(saved);
      return parsed.map((u) => {
        if (!u.imageUrl) {
          const match = INITIAL_UNITS.find((iu) => iu.code === u.code || iu.id === u.id);
          if (match?.imageUrl) return { ...u, imageUrl: match.imageUrl };
        }
        return u;
      });
    }
    return INITIAL_UNITS;
  });

  const [loans, setLoans] = useState<Loan[]>(() => {
    const saved = localStorage.getItem(STORAGE_KEY_PREFIX + 'loans');
    return saved ? JSON.parse(saved) : INITIAL_LOANS;
  });

  const [currentPage, setCurrentPage] = useState<PageRoute>(() => {
    const saved = localStorage.getItem(STORAGE_KEY_PREFIX + 'page');
    if (saved) return saved as PageRoute;
    return currentUser ? (currentUser.role === 'Admin' ? 'admin-dashboard' : 'survivor-dashboard') : 'login';
  });

  const [toasts, setToasts] = useState<ToastMessage[]>([]);

  // Sync to localStorage
  useEffect(() => {
    localStorage.setItem(STORAGE_KEY_PREFIX + 'users', JSON.stringify(users));
  }, [users]);

  useEffect(() => {
    localStorage.setItem(STORAGE_KEY_PREFIX + 'currentUser', JSON.stringify(currentUser));
  }, [currentUser]);

  useEffect(() => {
    localStorage.setItem(STORAGE_KEY_PREFIX + 'categories', JSON.stringify(categories));
  }, [categories]);

  useEffect(() => {
    localStorage.setItem(STORAGE_KEY_PREFIX + 'units', JSON.stringify(units));
  }, [units]);

  useEffect(() => {
    localStorage.setItem(STORAGE_KEY_PREFIX + 'loans', JSON.stringify(loans));
  }, [loans]);

  useEffect(() => {
    localStorage.setItem(STORAGE_KEY_PREFIX + 'page', currentPage);
  }, [currentPage]);

  // Toast handler
  const showToast = (message: string, type: 'success' | 'error' | 'warning' | 'info' = 'success') => {
    const id = Date.now().toString() + Math.random().toString(36).substring(2, 5);
    setToasts((prev) => [...prev, { id, message, type }]);
    setTimeout(() => {
      removeToast(id);
    }, 4000);
  };

  const removeToast = (id: string) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  };

  const getSurvivorActiveLoansCount = (survivorId: string) => {
    return loans.filter(
      (l) => l.survivorId === survivorId && (l.status === 'Aktif' || l.status === 'Terlambat')
    ).length;
  };

  // Stats calculation
  const totalUnits = units.length;
  const availableUnits = units.filter((u) => u.status === 'Tersedia').length;
  const borrowedUnits = units.filter((u) => u.status === 'Dipinjam').length;
  const overdueLoansCount = loans.filter((l) => l.status === 'Terlambat').length;

  // Login
  const login = (email: string, password: string) => {
    const found = users.find((u) => u.email.toLowerCase() === email.trim().toLowerCase());
    if (!found) {
      showToast('Kredensial tidak valid. Email tidak terdaftar.', 'error');
      return { success: false, message: 'Email tidak terdaftar.' };
    }
    if (found.password && found.password !== password) {
      showToast('Password salah. Silakan periksa kembali.', 'error');
      return { success: false, message: 'Password salah.' };
    }

    setCurrentUser(found);
    const targetPage: PageRoute = found.role === 'Admin' ? 'admin-dashboard' : 'survivor-dashboard';
    setCurrentPage(targetPage);
    return { success: true };
  };

  // Register (Survivor only)
  const register = (data: { name: string; email: string; phone: string; basecamp: string; password: string }) => {
    const emailExists = users.some((u) => u.email.toLowerCase() === data.email.trim().toLowerCase());
    if (emailExists) {
      showToast('Email sudah digunakan.', 'error');
      return { success: false, message: 'Email sudah digunakan.' };
    }

    const newUser: User = {
      id: 'usr-' + Date.now(),
      name: data.name.trim(),
      email: data.email.trim().toLowerCase(),
      phone: data.phone.trim(),
      basecamp: data.basecamp.trim(),
      role: 'Survivor',
      password: data.password,
    };

    setUsers((prev) => [...prev, newUser]);
    setCurrentUser(newUser);
    setCurrentPage('survivor-dashboard');
    showToast('Registrasi survivor berhasil. Akun aktif.', 'success');
    return { success: true };
  };

  // Logout
  const logout = () => {
    setCurrentUser(null);
    setCurrentPage('login');
    showToast('Sesi telah diakhiri.', 'info');
  };

  // Switch role for quick development demo testing
  const switchRoleDev = (role: UserRole, specificUserId?: string) => {
    if (specificUserId) {
      const targetUser = users.find((u) => u.id === specificUserId);
      if (targetUser) {
        setCurrentUser(targetUser);
        setCurrentPage(targetUser.role === 'Admin' ? 'admin-dashboard' : 'survivor-dashboard');
        showToast(`Beralih ke akun ${targetUser.name} (${targetUser.role}).`, 'info');
        return;
      }
    }

    const targetUser = users.find((u) => u.role === role);
    if (targetUser) {
      setCurrentUser(targetUser);
      setCurrentPage(role === 'Admin' ? 'admin-dashboard' : 'survivor-dashboard');
      showToast(`Mode role beralih ke: ${role} (${targetUser.name}).`, 'info');
    }
  };

  // Categories
  const addCategory = (name: string) => {
    const trimmed = name.trim();
    if (!trimmed) {
      return { success: false, message: 'Nama kategori wajib diisi.' };
    }
    const exists = categories.some((c) => c.name.toLowerCase() === trimmed.toLowerCase());
    if (exists) {
      return { success: false, message: 'Kategori dengan nama tersebut sudah ada.' };
    }

    const newCat: Category = {
      id: 'cat-' + Date.now(),
      name: trimmed,
    };
    setCategories((prev) => [...prev, newCat]);
    showToast('Data berhasil disimpan.', 'success');
    return { success: true };
  };

  const updateCategory = (id: string, name: string) => {
    const trimmed = name.trim();
    if (!trimmed) {
      return { success: false, message: 'Nama kategori wajib diisi.' };
    }
    const exists = categories.some((c) => c.id !== id && c.name.toLowerCase() === trimmed.toLowerCase());
    if (exists) {
      return { success: false, message: 'Kategori dengan nama tersebut sudah ada.' };
    }

    setCategories((prev) => prev.map((c) => (c.id === id ? { ...c, name: trimmed } : c)));
    showToast('Data berhasil disimpan.', 'success');
    return { success: true };
  };

  const deleteCategory = (id: string) => {
    // Check if category is used by any unit
    const isUsed = units.some((u) => u.categoryIds.includes(id));
    if (isUsed) {
      showToast('Kategori masih digunakan oleh unit inventaris.', 'error');
      return { success: false, message: 'Kategori masih digunakan oleh unit inventaris.' };
    }

    setCategories((prev) => prev.filter((c) => c.id !== id));
    showToast('Kategori berhasil dihapus.', 'success');
    return { success: true };
  };

  // Units
  const addUnit = (data: { code: string; name: string; description: string; categoryIds: string[]; imageUrl?: string }) => {
    const trimmedCode = data.code.trim().toUpperCase();
    if (!trimmedCode) return { success: false, message: 'Kode unit wajib diisi.' };
    if (!data.name.trim()) return { success: false, message: 'Nama unit wajib diisi.' };
    if (!data.categoryIds || data.categoryIds.length === 0) {
      return { success: false, message: 'Pilih minimal satu kategori.' };
    }

    // Check unique code
    const codeExists = units.some((u) => u.code.toUpperCase() === trimmedCode);
    if (codeExists) {
      return { success: false, message: 'Kode unit sudah digunakan.' };
    }

    const newUnit: Unit = {
      id: 'unit-' + Date.now(),
      code: trimmedCode,
      name: data.name.trim(),
      description: data.description.trim(),
      categoryIds: data.categoryIds,
      status: 'Tersedia',
      imageUrl: data.imageUrl?.trim() || undefined,
    };

    setUnits((prev) => [newUnit, ...prev]);
    showToast('Data berhasil disimpan.', 'success');
    return { success: true };
  };

  const updateUnit = (id: string, data: { code: string; name: string; description: string; categoryIds: string[]; imageUrl?: string }) => {
    const trimmedCode = data.code.trim().toUpperCase();
    if (!trimmedCode) return { success: false, message: 'Kode unit wajib diisi.' };
    if (!data.name.trim()) return { success: false, message: 'Nama unit wajib diisi.' };
    if (!data.categoryIds || data.categoryIds.length === 0) {
      return { success: false, message: 'Pilih minimal satu kategori.' };
    }

    const codeExists = units.some((u) => u.id !== id && u.code.toUpperCase() === trimmedCode);
    if (codeExists) {
      return { success: false, message: 'Kode unit sudah digunakan.' };
    }

    setUnits((prev) =>
      prev.map((u) =>
        u.id === id
          ? {
              ...u,
              code: trimmedCode,
              name: data.name.trim(),
              description: data.description.trim(),
              categoryIds: data.categoryIds,
              imageUrl: data.imageUrl !== undefined ? (data.imageUrl.trim() || undefined) : u.imageUrl,
            }
          : u
      )
    );
    showToast('Data berhasil disimpan.', 'success');
    return { success: true };
  };

  const deleteUnit = (id: string) => {
    const target = units.find((u) => u.id === id);
    if (!target) return { success: false, message: 'Unit tidak ditemukan.' };

    if (target.status === 'Dipinjam') {
      showToast('Unit sedang dipinjam dan tidak dapat dihapus.', 'error');
      return { success: false, message: 'Unit sedang dipinjam.' };
    }

    setUnits((prev) => prev.filter((u) => u.id !== id));
    showToast('Unit berhasil dihapus.', 'success');
    return { success: true };
  };

  // Member creation by Admin
  const addMember = (data: {
    name: string;
    email: string;
    phone: string;
    basecamp: string;
    password?: string;
    role?: UserRole;
  }) => {
    const emailExists = users.some(
      (u) => u.email.toLowerCase() === data.email.trim().toLowerCase()
    );
    if (emailExists) {
      showToast('Email sudah terdaftar untuk personel lain.', 'error');
      return { success: false, message: 'Email sudah terdaftar.' };
    }

    const newUser: User = {
      id: 'usr-' + Date.now().toString().slice(-4),
      name: data.name.trim(),
      email: data.email.trim().toLowerCase(),
      phone: data.phone.trim(),
      basecamp: data.basecamp.trim(),
      role: data.role || 'Survivor',
      password: data.password?.trim() || 'password123',
    };

    setUsers((prev) => [...prev, newUser]);
    showToast(`Personel baru "${newUser.name}" berhasil didaftarkan ke sektor.`, 'success');
    return { success: true };
  };

  // Member update by Admin
  const updateMember = (id: string, data: Partial<User>) => {
    const existing = users.find((u) => u.id === id);
    if (!existing) {
      showToast('Personel tidak ditemukan.', 'error');
      return { success: false, message: 'Personel tidak ditemukan.' };
    }

    if (data.email && data.email.trim().toLowerCase() !== existing.email.toLowerCase()) {
      const emailExists = users.some(
        (u) => u.id !== id && u.email.toLowerCase() === data.email?.trim().toLowerCase()
      );
      if (emailExists) {
        showToast('Email sudah digunakan oleh personel lain.', 'error');
        return { success: false, message: 'Email sudah terdaftar.' };
      }
    }

    setUsers((prev) =>
      prev.map((u) => {
        if (u.id === id) {
          return {
            ...u,
            ...data,
            name: data.name !== undefined ? data.name.trim() : u.name,
            email: data.email !== undefined ? data.email.trim().toLowerCase() : u.email,
            phone: data.phone !== undefined ? data.phone.trim() : u.phone,
            basecamp: data.basecamp !== undefined ? data.basecamp.trim() : u.basecamp,
          };
        }
        return u;
      })
    );
    showToast(`Data personel "${data.name || existing.name}" berhasil diperbarui.`, 'success');
    return { success: true };
  };

  // Member deletion
  const deleteMember = (id: string) => {
    const activeCount = getSurvivorActiveLoansCount(id);
    if (activeCount > 0) {
      showToast('Anggota memiliki pinjaman aktif dan tidak dapat dihapus.', 'error');
      return { success: false, message: 'Anggota memiliki pinjaman aktif.' };
    }

    setUsers((prev) => prev.filter((u) => u.id !== id));
    showToast('Anggota berhasil dihapus.', 'success');
    return { success: true };
  };

  // Borrow unit (Survivor only)
  const borrowUnit = (unitId: string) => {
    if (!currentUser || currentUser.role !== 'Survivor') {
      showToast('Hanya survivor yang dapat meminjam unit.', 'error');
      return { success: false, message: 'Otorisasi tidak sesuai.' };
    }

    const activeLoans = getSurvivorActiveLoansCount(currentUser.id);
    if (activeLoans >= MAX_ACTIVE_LOANS) {
      showToast('Batas pinjaman tercapai (Maksimal 2 unit aktif).', 'warning');
      return { success: false, message: 'Batas pinjaman tercapai.' };
    }

    const targetUnit = units.find((u) => u.id === unitId);
    if (!targetUnit) {
      showToast('Unit tidak ditemukan.', 'error');
      return { success: false, message: 'Unit tidak ditemukan.' };
    }

    if (targetUnit.status !== 'Tersedia') {
      showToast('Unit sedang dipinjam.', 'error');
      return { success: false, message: 'Unit sedang dipinjam.' };
    }

    const today = new Date();
    const loanDateStr = today.toISOString().split('T')[0];
    const dueDate = new Date(today);
    dueDate.setDate(dueDate.getDate() + LOAN_DURATION_DAYS);
    const dueDateStr = dueDate.toISOString().split('T')[0];

    const newLoan: Loan = {
      id: 'loan-' + Date.now(),
      survivorId: currentUser.id,
      survivorName: currentUser.name,
      survivorEmail: currentUser.email,
      unitId: targetUnit.id,
      unitCode: targetUnit.code,
      unitName: targetUnit.name,
      loanDate: loanDateStr,
      dueDate: dueDateStr,
      overdueDays: 0,
      fineAmount: 0,
      status: 'Aktif',
    };

    // Update loan list
    setLoans((prev) => [newLoan, ...prev]);

    // Update unit status
    setUnits((prev) =>
      prev.map((u) => (u.id === unitId ? { ...u, status: 'Dipinjam' } : u))
    );

    showToast(`Peminjaman ${targetUnit.name} berhasil diajukan.`, 'success');
    return { success: true };
  };

  // Process return (Admin only)
  const processReturn = (loanId: string, returnDateStr?: string) => {
    const targetLoan = loans.find((l) => l.id === loanId);
    if (!targetLoan) {
      showToast('Data peminjaman tidak ditemukan.', 'error');
      return { success: false, message: 'Peminjaman tidak ditemukan.' };
    }

    const retDate = returnDateStr ? new Date(returnDateStr) : new Date();
    const retDateFormatted = retDate.toISOString().split('T')[0];
    const dueDate = new Date(targetLoan.dueDate);

    // Calculate difference in calendar days
    const diffTime = retDate.getTime() - dueDate.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    const overdueDays = Math.max(0, diffDays);
    const fineAmount = overdueDays * DAILY_FINE_RATE;

    // Update loan
    setLoans((prev) =>
      prev.map((l) =>
        l.id === loanId
          ? {
              ...l,
              returnDate: retDateFormatted,
              overdueDays,
              fineAmount,
              status: 'Selesai',
            }
          : l
      )
    );

    // Update unit status to 'Tersedia'
    setUnits((prev) =>
      prev.map((u) => (u.id === targetLoan.unitId ? { ...u, status: 'Tersedia' } : u))
    );

    showToast('Pengembalian berhasil diproses.', 'success');
    return { success: true };
  };

  // Profile update
  const updateProfile = (data: Partial<User>) => {
    if (!currentUser) return { success: false, message: 'Tidak ada sesi aktif.' };

    const updatedUser = { ...currentUser, ...data };
    setCurrentUser(updatedUser);
    setUsers((prev) => prev.map((u) => (u.id === currentUser.id ? updatedUser : u)));
    showToast('Data berhasil disimpan.', 'success');
    return { success: true };
  };

  // Change password
  const changePassword = (newPass: string) => {
    if (!currentUser) return { success: false, message: 'Tidak ada sesi aktif.' };
    const updatedUser = { ...currentUser, password: newPass };
    setCurrentUser(updatedUser);
    setUsers((prev) => prev.map((u) => (u.id === currentUser.id ? updatedUser : u)));
    showToast('Password berhasil diperbarui.', 'success');
    return { success: true };
  };

  // Reset to initial demo seed
  const resetAllData = () => {
    localStorage.clear();
    setUsers(INITIAL_USERS);
    setCurrentUser(INITIAL_USERS[0]);
    setCategories(INITIAL_CATEGORIES);
    setUnits(INITIAL_UNITS);
    setLoans(INITIAL_LOANS);
    setCurrentPage('admin-dashboard');
    showToast('Semua data simulasi telah di-reset ke nilai awal.', 'info');
  };

  return (
    <DeadlockContext.Provider
      value={{
        currentUser,
        users,
        categories,
        units,
        loans,
        currentPage,
        toasts,
        stats: {
          totalUnits,
          availableUnits,
          borrowedUnits,
          overdueLoansCount,
        },
        setCurrentPage,
        login,
        register,
        logout,
        switchRoleDev,
        addCategory,
        updateCategory,
        deleteCategory,
        addUnit,
        updateUnit,
        deleteUnit,
        addMember,
        updateMember,
        deleteMember,
        borrowUnit,
        processReturn,
        updateProfile,
        changePassword,
        showToast,
        removeToast,
        getSurvivorActiveLoansCount,
        resetAllData,
      }}
    >
      {children}
    </DeadlockContext.Provider>
  );
};

export const useDeadlock = () => {
  const context = useContext(DeadlockContext);
  if (!context) {
    throw new Error('useDeadlock must be used within a DeadlockProvider');
  }
  return context;
};
