import { Clock } from 'lucide-react';
import { STATUS_LABELS } from '../lib/statusLabels.js';

export function StatusPill({ status, loading }) {
  if (loading) {
    return (
      <span className="status-pill muted">
        <Clock size={14} />
        Chargement
      </span>
    );
  }

  if (!status) return null;

  return (
    <span className={`status-pill ${status}`}>
      <Clock size={14} />
      {STATUS_LABELS[status] || status}
    </span>
  );
}
