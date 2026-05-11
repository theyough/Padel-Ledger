import { Check, X } from 'lucide-react';

export function ValidationList({ proposal }) {
  return (
    <div className="validation-list">
      {proposal.validations.map((validation) => (
        <div className={`validation-item ${validation.decision}`} key={validation.player.id}>
          {validation.decision === 'approved' ? <Check size={16} /> : <X size={16} />}
          <span>{validation.player.fullName}</span>
          {validation.comment && <small>{validation.comment}</small>}
        </div>
      ))}
    </div>
  );
}
