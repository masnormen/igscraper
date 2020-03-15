# igscrap
An API to get basic Instagram account information, post count, and last posts information. Uses SimpleCache library by Gilbert Pellegrom

Forked from [instagram-php-scraper](https://github.com/jonlovera/instagram-php-scraper) by jonlovera. Copied from the description:

```
instagram-php-scraper is a simple file written in PHP that scrapes and show in a JSON format an instagram user's photos, likes, videos, etc. Use responsibly.
```

## URL
`/index.php`

## Method:
`GET`

## Parameter:
- `username: string`
- `posts: integer` (optional; default, max: 12)

## Response:
```
{
    status
    followers
    following
    totalPost
    post [
        title
        link
        date
        thumbnail
        caption
    ]
}
```