import { LogOut, RefreshCw, Trophy, Users } from 'lucide-react';
import { Notice } from './Notice.jsx';
import { StatusPill } from './StatusPill.jsx';

export function AuthenticatedShell({
  player,
  headerTitle,
  loading,
  error,
  message,
  statusPillStatus,
  onDashboard,
  onRefresh,
  onLogout,
  children,
}) {
  return (
    <div className="app-shell">
      <aside className="sidebar">
        <div className="brand">
          <Trophy size={24} />
          <div>
            <strong>Niveaux padel</strong>
            <span>Gestion de club</span>
          </div>
        </div>

        <div className="profile-card">
          <div className="level-badge">N{player.level}</div>
          <div>
            <strong>{player.fullName}</strong>
            <span>{Math.round(player.rating)} pts · {player.matchCount} matchs</span>
          </div>
        </div>

        <button type="button" className="nav-button" onClick={onDashboard}>
          <Users size={18} />
          Tableau de bord
        </button>
        <button type="button" className="nav-button" onClick={onRefresh}>
          <RefreshCw size={18} />
          Actualiser
        </button>
        <button type="button" className="nav-button danger" onClick={onLogout}>
          <LogOut size={18} />
          Déconnexion
        </button>
      </aside>

      <main className="content">
        <header className="topbar">
          <div>
            <p className="eyebrow">Gestion des niveaux</p>
            <h1>{headerTitle}</h1>
          </div>
          <StatusPill status={statusPillStatus} loading={loading} />
        </header>

        {error && <Notice type="error" text={error} />}
        {message && <Notice type="success" text={message} />}

        {children}
      </main>
    </div>
  );
}
