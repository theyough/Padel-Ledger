export function RangeField({ label, value, onChange }) {
  return (
    <label className="range-field">
      <span>{label}</span>
      <input type="range" min="1" max="8" value={value} onChange={(event) => onChange(Number(event.target.value))} />
      <strong>{value}</strong>
    </label>
  );
}
