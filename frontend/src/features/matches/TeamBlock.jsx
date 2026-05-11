export function TeamBlock({ title, players }) {
  return (
    <div className="team-block">
      <span>{title}</span>
      {players.map((player) => (
        <strong key={player.id}>{player.fullName} <em>N{player.level}</em></strong>
      ))}
    </div>
  );
}
