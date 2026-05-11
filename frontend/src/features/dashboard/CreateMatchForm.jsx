import { useState } from 'react';
import { Plus } from 'lucide-react';
import { apiRequest } from '../../api.js';
import { asMatch } from '../../lib/hydra.js';
import { TeamSelect } from './TeamSelect.jsx';

export function CreateMatchForm({ players, currentPlayer, token, runAction, onCreated }) {
  const [teamA, setTeamA] = useState([currentPlayer.id, '']);
  const [teamB, setTeamB] = useState(['', '']);
  const [scheduledAt, setScheduledAt] = useState('');

  async function submit(event) {
    event.preventDefault();
    const result = await runAction(async () => {
      return apiRequest('/api/matches', {
        token,
        method: 'POST',
        body: {
          teamA: teamA.map(Number),
          teamB: teamB.map(Number),
          scheduledAt: scheduledAt || null,
        },
      });
    }, 'Match créé.');

    if (result) {
      onCreated(asMatch(result));
    }
  }

  return (
    <form className="form-stack compact-form" onSubmit={submit}>
      <TeamSelect title="Équipe A" values={teamA} players={players} onChange={setTeamA} />
      <TeamSelect title="Équipe B" values={teamB} players={players} onChange={setTeamB} />
      <label>
        Date
        <input type="datetime-local" value={scheduledAt} onChange={(event) => setScheduledAt(event.target.value)} />
      </label>
      <button className="primary-button" type="submit" disabled={players.length < 4}>
        <Plus size={18} />
        Enregistrer
      </button>
      {players.length < 4 && <p className="hint">Au moins 4 comptes joueurs sont nécessaires pour créer un match.</p>}
    </form>
  );
}
