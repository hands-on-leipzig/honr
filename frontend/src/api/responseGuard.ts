/**
 * Check if API response data looks like JSON (array or object), not HTML.
 * When the server returns the SPA HTML for API routes (e.g. wrong rewrite), we must not treat it as data.
 */
export function isJsonArray(data: unknown): data is unknown[] {
  return Array.isArray(data)
}

export function isJsonObject(data: unknown): data is Record<string, unknown> {
  return typeof data === 'object' && data !== null && !Array.isArray(data)
}

/** Options object with programs, seasons, levels arrays (leaderboard/heatmap) */
export function isFilterOptionsShape(data: unknown): data is { programs: unknown[]; seasons: unknown[]; levels: unknown[] } {
  return isJsonObject(data) && Array.isArray((data as Record<string, unknown>).programs) && Array.isArray((data as Record<string, unknown>).seasons) && Array.isArray((data as Record<string, unknown>).levels)
}

/** Likely HTML (e.g. SPA fallback) instead of JSON */
export function looksLikeHtml(data: unknown): boolean {
  return typeof data === 'string' && data.trim().startsWith('<')
}

export const LOAD_ERROR_MESSAGE =
  'Daten konnten nicht geladen werden. Bitte die Verbindung prüfen oder später erneut versuchen.'
