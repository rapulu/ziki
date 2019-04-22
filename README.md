# ziki

ziki is a blogging service like wordpress it runs on your own self-hosted domain. Think of it as a distributed social network.
It does not run on a url like `medium.com/markessien` or `wordpress.com/markessien`. I can install ziki on my own personal domain and set up my own blog.

I can install ziki on `markessien.com` or on a custom domain of my choosing.

Ziki relies on the use of two terms:

`Owner` and `Guest`

## The owner
This is the person that downloaded ziki and installed it on their domain. The owner has permission to view their timeline, write and publish posts and follow other `ziki` users' posts using RSS. The owner only has an additional link called `timeline` which is private. The owner can choose to make their timeline public.

## The Guest

This is the person visiting a `ziki` site. When on the site, the users can view posts by the site [owner](##The-owner). If a guest wants to interact with the posts i.e like or comment they can login to the site to access these privilleges. If the site uses [disqus](https://disqus.com/) for commenting, the guest has no access to native login only the site [owner](##The-owner) does.

### Contributing

- Make sure you have setup your `PHP` development environment
- Ensure `composer` is setup on your development environment
- Ensure you have the current lts version of [NodeJs](https://nodejs.org)
- Fork your own copy of the repository
- Clone it
- Run `composer install --no-dev`
- Run `npm install`, `npm run assets -- <theme-name>` and then `npm start -- <theme-name>`.
  For the current theme, these commands would be `npm run assets -- ghost` and then `npm start -- ghost`.
- Go to `localhost:8000` on your browser and you are good to go

#### The Pull Request Template:

```
PR Tittle : #[STORY_ID] Story description

#### What does this PR do?
#### Description of Task to be completed?
#### How should this be manually tested?
#### Any background context you want to provide?
#### What are the relevant pivotal tracker stories?
#### Screenshots (if appropriate)
#### Questions/Comments:
```

#### Pull Request Example:

```
#### What does this PR do?
* It creates the landing page
#### Description of Task to be completed?
* Landing page `index.html` is created
* Styling is added to `styles.css`
#### How should this be manually tested?
* Open the site on your browser
* Type localhost/home
* You should see the landing page
#### Any background context you want to provide?
* use gulp to automate builds
#### What are the relevant pivotal tracker stories?
[#123456](https://www.pivotaltracker.com/story/show/123456)
#### Screenshots (if appropriate)
#### Questions/Comments:
```
