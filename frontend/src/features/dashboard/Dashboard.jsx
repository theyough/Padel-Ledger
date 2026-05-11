import { Plus } from 'lucide-react';
import { EmptyState } from '../../components/EmptyState.jsx';
import { StatusPill } from '../../components/StatusPill.jsx';
import { teamLabel } from '../../lib/matchDisplay.js';
import { CreateMatchForm } from './CreateMatchForm.jsx';

export function Dashboard({ players, matches, currentPlayer, token, runAction, onSelectMatch }) {
  return (
    <div className="dashboard-grid">
      <section className="panel match-list-panel">
        <div className="panel-heading">
          <div>
            <p className="eyebrow">Matchs</p>
            <h2>Mes derniers matchs</h2>
          </div>
          <span className="count-badge">{matches.length}</span>
        </div>
        <div className="match-list">
          {matches.length === 0 && <EmptyState text="Aucun match pour le moment." />}
          {matches.map((match) => (
            <button type="button" className="match-item" key={match.id} onClick={() => onSelectMatch(match.id)}>
              <div>
                <strong>Match n°{match.id}</strong>
                <span>{teamLabel(match.teamA)} contre {teamLabel(match.teamB)}</span>
              </div>
              <StatusPill status={match.status} />
            </button>
          ))}
        </div>
      </section>

      <section className="panel">
        <div className="panel-heading">
          <div>
            <p className="eyebrow">Nouveau</p>
            <h2>Créer un match</h2>
          </div>
          <Plus size={20} />
        </div>
        <CreateMatchForm
          players={players}
          currentPlayer={currentPlayer}
          token={token}
          runAction={runAction}
          onCreated={(match) => onSelectMatch(match.id)}
        />
      </section>
    </div>
  );
}
