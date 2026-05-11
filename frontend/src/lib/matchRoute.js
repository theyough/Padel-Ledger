export function readInitialMatchId() {
  const match = window.location.pathname.match(/^\/matches\/(\d+)/);
  return match ? Number(match[1]) : null;
}
