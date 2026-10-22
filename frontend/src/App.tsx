import React from 'react';
import { DeadlockProvider, useDeadlock } from './context/DeadlockContext';
import { AppLayout } from './components/layout/AppLayout';
import { LoginPage } from './pages/auth/LoginPage';
import { RegisterPage } from './pages/auth/RegisterPage';
import { AdminDashboardPage } from './pages/admin/AdminDashboardPage';
import { CategoriesPage } from './pages/admin/CategoriesPage';
import { UnitsPage } from './pages/admin/UnitsPage';
import { MembersPage } from './pages/admin/MembersPage';
import { LoansPage } from './pages/admin/LoansPage';
import { LoanHistoryPage } from './pages/admin/LoanHistoryPage';
import { SurvivorDashboardPage } from './pages/survivor/SurvivorDashboardPage';
import { CatalogPage } from './pages/survivor/CatalogPage';
import { MyLoansPage } from './pages/survivor/MyLoansPage';
import { ProfilePage } from './pages/common/ProfilePage';
import './App.css';

const MainRouter: React.FC = () => {
  const { currentUser, currentPage } = useDeadlock();

  // If not logged in and not on login or register, direct to login
  if (!currentUser && currentPage !== 'login' && currentPage !== 'register') {
    return <LoginPage />;
  }

  // Auth pages (no dashboard layout)
  if (currentPage === 'login') {
    return <LoginPage />;
  }
  if (currentPage === 'register') {
    return <RegisterPage />;
  }

  // Role guard
  const isAdmin = currentUser?.role === 'Admin';

  const renderActivePage = () => {
    switch (currentPage) {
      // Admin pages
      case 'admin-dashboard':
        return isAdmin ? <AdminDashboardPage /> : <SurvivorDashboardPage />;
      case 'admin-categories':
        return isAdmin ? <CategoriesPage /> : <CatalogPage />;
      case 'admin-units':
        return isAdmin ? <UnitsPage /> : <CatalogPage />;
      case 'admin-members':
        return isAdmin ? <MembersPage /> : <SurvivorDashboardPage />;
      case 'admin-loans':
        return isAdmin ? <LoansPage /> : <MyLoansPage />;
      case 'admin-history':
        return isAdmin ? <LoanHistoryPage /> : <MyLoansPage />;

      // Survivor pages
      case 'survivor-dashboard':
        return !isAdmin ? <SurvivorDashboardPage /> : <AdminDashboardPage />;
      case 'survivor-catalog':
        return !isAdmin ? <CatalogPage /> : <UnitsPage />;
      case 'survivor-loans':
        return !isAdmin ? <MyLoansPage /> : <LoansPage />;

      // Shared
      case 'profile':
        return <ProfilePage />;

      default:
        return isAdmin ? <AdminDashboardPage /> : <SurvivorDashboardPage />;
    }
  };

  return <AppLayout>{renderActivePage()}</AppLayout>;
};

export function App() {
  return (
    <DeadlockProvider>
      <MainRouter />
    </DeadlockProvider>
  );
}

export default App;
