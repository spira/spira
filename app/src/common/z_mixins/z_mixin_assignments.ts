namespace common.mixins {

    applyMixins(common.models.Article, [SectionableModel]);
    applyMixins(app.admin.articles.article.content.ContentController, [SectionableController]);
    applyMixins(common.models.Article, [SectionableModel, TaggableModel, LocalizableModel]);
    applyMixins(common.services.article.ArticleService, [SectionableApiService, TaggableApiService, LocalizableApiService]);

}