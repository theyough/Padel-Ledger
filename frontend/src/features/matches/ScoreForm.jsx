import { useState } from 'react';
import { Check } from 'lucide-react';
import { apiRequest } from '../../api.js';
import { ScoreSetRow } from './ScoreSetRow.jsx';

export function ScoreForm({ matchId, token, runAction, label }) {
  const [sets, setSets] = useState([
    { teamA: 6, teamB: 4 },
    { teamA: 6, teamB: 4 },
    { teamA: 0, teamB: 0 },
  ]);

  function updateSet(index, side, value) {
    setSets((current) => current.map((set, setIndex) => (
      setIndex === index ? { ...set, [side]: Number(value) } : set
    )));
  }

  async function submit(event) {
    event.preventDefault();
    await runAction(
      () => apiRequest(`/api/matches/${matchId}/score-proposals`, {
        token,
        method: 'POST',
        body: { sets },
      }),
      'Score proposé.'
    );
  }

  return (
    <form className="form-stack compact-form" onSubmit={submit}>
      <div className="score-grid">
        <span />
        <strong>Équipe A</strong>
        <strong>Équipe B</strong>
        {sets.map((set, index) => (
          <ScoreSetRow key={index} index={index} set={set} updateSet={updateSet} />
        ))}
      </div>
      <button className="primary-button" type="submit">
        <Check size={18} />
        {label}
      </button>
    </form>
  );
}
