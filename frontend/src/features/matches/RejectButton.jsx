import { useState } from 'react';
import { X } from 'lucide-react';
import { apiRequest } from '../../api.js';

export function RejectButton({ matchId, token, runAction }) {
  const [open, setOpen] = useState(false);
  const [comment, setComment] = useState('');

  if (!open) {
    return (
      <button type="button" className="danger-button" onClick={() => setOpen(true)}>
        <X size={18} />
        Refuser
      </button>
    );
  }

  return (
    <div className="reject-box">
      <input value={comment} onChange={(event) => setComment(event.target.value)} placeholder="Motif (court)" />
      <button
        type="button"
        className="danger-button"
        onClick={() => runAction(
          () => apiRequest(`/api/matches/${matchId}/score-proposals/current/reject`, {
            token,
            method: 'POST',
            body: { comment },
          }),
          'Refus enregistré.'
        )}
      >
        <X size={18} />
        Confirmer
      </button>
    </div>
  );
}
