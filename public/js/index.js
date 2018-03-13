function getQueryStrings() {
  let queries = []
  let query = null
  let _queries = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&')
  for (let i = 0; i < _queries.length; i++) {
    query = _queries[i].split('=')
    queries[query[0]] = query[1]
  }
  return queries
}

let queries = getQueryStrings()

if (queries['fail'] === '0') {
  alert('您的帳號或密碼輸入錯誤！')
}
