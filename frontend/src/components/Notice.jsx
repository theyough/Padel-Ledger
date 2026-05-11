import { Check, CircleAlert } from 'lucide-react';

export function Notice({ type, text }) {
  return (
    <div className={`notice ${type}`}>
      {type === 'error' ? <CircleAlert size={18} /> : <Check size={18} />}
      {text}
    </div>
  );
}
