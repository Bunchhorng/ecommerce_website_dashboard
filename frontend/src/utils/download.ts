import type { AxiosResponse } from 'axios'

export function downloadBlob(blob: Blob, filename: string): void {
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)
}

export function downloadResponse(response: AxiosResponse<Blob>, fallbackFilename: string): void {
  let filename = fallbackFilename
  const disposition = response.headers['content-disposition'] as string | undefined
  if (disposition) {
    const match = /filename="?([^";]+)"?/.exec(disposition)
    if (match?.[1]) {
      filename = match[1]
    }
  }
  downloadBlob(response.data, filename)
}