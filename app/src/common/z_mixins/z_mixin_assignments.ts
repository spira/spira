namespace common.mixins {

    applyMixins(common.models.Article, [SectionableModel, LocalizableModel]);

    applyMixins(app.admin.articles.article.content.ContentController, [SectionableController]);

    applyMixins(common.services.article.ArticleService, [SectionableApiService, TaggableApiService, LocalizableApiService, MetaableApiService]);

}